import BidModel from "../models/BidModel.js";
import AlumniEventModel from "../models/AlumniEventModel.js";
import SponsorshipOfferModel from "../models/SponsorshipOfferModel.js";
import { pool } from "../config/database.js";
import { logger } from "../config/logger.js";

class BiddingService {
    static getBiddingConfig() {
        return {
            timezone: process.env.BID_TIMEZONE || "Europe/London",
            cutoffHour: parseInt(process.env.BID_CUTOFF_HOUR, 10) || 18,
            cutoffMinute: parseInt(process.env.BID_CUTOFF_MINUTE, 10) || 0,
        };
    }

    static getDatePartsInTimeZone(date, timezone) {
        const formatter = new Intl.DateTimeFormat("en-CA", {
            timeZone: timezone,
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            hourCycle: "h23",
        });

        const parts = formatter.formatToParts(date);
        const values = Object.fromEntries(
            parts
                .filter((part) => part.type !== "literal")
                .map((part) => [part.type, part.value])
        );

        return {
            year: parseInt(values.year, 10),
            month: parseInt(values.month, 10),
            day: parseInt(values.day, 10),
            hour: parseInt(values.hour, 10),
            minute: parseInt(values.minute, 10),
        };
    }

    static formatDateKey(year, month, day) {
        return [
            String(year).padStart(4, "0"),
            String(month).padStart(2, "0"),
            String(day).padStart(2, "0"),
        ].join("-");
    }

    static getDateKeyFromValue(value) {
        if (value === null || value === undefined) {
            return null;
        }

        if (value instanceof Date) {
            return this.formatDateKey(value.getFullYear(), value.getMonth() + 1, value.getDate());
        }

        const stringValue = String(value);
        const match = stringValue.match(/(\\d{4})-(\\d{2})-(\\d{2})/);
        if (!match) {
            return null;
        }

        const [, year, month, day] = match;
        return this.formatDateKey(year, month, day);
    }

    static getTomorrowDateKeyInTimeZone(timezone) {
        const nowParts = this.getDatePartsInTimeZone(new Date(), timezone);
        const localDateAnchor = new Date(Date.UTC(nowParts.year, nowParts.month - 1, nowParts.day, 12, 0, 0));
        localDateAnchor.setUTCDate(localDateAnchor.getUTCDate() + 1);

        return this.formatDateKey(
            localDateAnchor.getUTCFullYear(),
            localDateAnchor.getUTCMonth() + 1,
            localDateAnchor.getUTCDate()
        );
    }

    static enforceTomorrowOnlyBidDate(bidDate) {
        const { timezone } = this.getBiddingConfig();
        const tomorrowDateKey = this.getTomorrowDateKeyInTimeZone(timezone);

        if (bidDate === tomorrowDateKey) {
            return;
        }

        const error = new Error(`Bids can only be placed for tomorrow's featured slot (${tomorrowDateKey}).`);
        error.code = "INVALID_BID_DATE";
        error.status = 400;
        throw error;
    }

    static enforceTomorrowBidCutoff(bidDate) {
        const { timezone, cutoffHour, cutoffMinute } = this.getBiddingConfig();
        const nowParts = this.getDatePartsInTimeZone(new Date(), timezone);
        const tomorrowDateKey = this.getTomorrowDateKeyInTimeZone(timezone);

        if (bidDate !== tomorrowDateKey) {
            return;
        }

        const hasPassedCutoff =
            nowParts.hour > cutoffHour ||
            (nowParts.hour === cutoffHour && nowParts.minute >= cutoffMinute);

        if (!hasPassedCutoff) {
            return;
        }

        const error = new Error(
            `Bidding for ${bidDate} closed at ${String(cutoffHour).padStart(2, "0")}:${String(cutoffMinute).padStart(2, "0")} ${timezone}.`
        );
        error.code = "BID_CUTOFF_PASSED";
        error.status = 400;
        throw error;
    }

    static async validateSpecificSponsorshipOffer(userId, sponsorshipOfferId, bidAmount) {
        if (sponsorshipOfferId === null) {
            return null;
        }

        const offer = await SponsorshipOfferModel.findAcceptedAvailableByIdAndUser(sponsorshipOfferId, userId);
        if (!offer) {
            const error = new Error("Selected sponsorship offer is not available for this user.");
            error.code = "INVALID_SPONSORSHIP_OFFER";
            error.status = 400;
            throw error;
        }

        if (bidAmount > parseFloat(offer.remaining_amount)) {
            const error = new Error(
                `Bid exceeds the selected sponsorship offer balance. Available: ${parseFloat(offer.remaining_amount).toFixed(2)}`
            );
            error.code = "INSUFFICIENT_OFFER_FUNDS";
            error.status = 400;
            throw error;
        }

        return offer;
    }

    static async placeBid(userId, bidAmount, bidDate, sponsorshipOfferId = null) {
        this.enforceTomorrowOnlyBidDate(bidDate);
        this.enforceTomorrowBidCutoff(bidDate);

        const existingBid = await BidModel.findActiveByUserAndDate(userId, bidDate);
        if (existingBid) {
            const error = new Error("You already have an active bid for this date.");
            error.code = "DUPLICATE_BID";
            error.status = 409;
            throw error;
        }

        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT, 10) || 3;
        const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT, 10) || 4;
        const monthlyRecord = await BidModel.getMonthlyCount(userId, year, month);

        if (monthlyRecord) {
            const currentCount = monthlyRecord.count;
            const attendedEvent = Boolean(monthlyRecord.attended_event);
            const maxAllowed = attendedEvent ? eventLimit : normalLimit;

            if (currentCount >= maxAllowed) {
                const error = new Error(`Monthly feature limit reached (${currentCount}/${maxAllowed}).`);
                error.code = "MONTHLY_LIMIT_REACHED";
                error.status = 403;
                throw error;
            }
        }

        const balance = await BidModel.getAvailableSponsorshipBalance(userId);
        const availableBalance = balance.available_balance;
        if (bidAmount > availableBalance) {
            const error = new Error(
                `Insufficient sponsorship funds. Available: ${availableBalance.toFixed(2)}, Requested: ${bidAmount.toFixed(2)}`
            );
            error.code = "INSUFFICIENT_FUNDS";
            error.status = 400;
            throw error;
        }

        await this.validateSpecificSponsorshipOffer(userId, sponsorshipOfferId, bidAmount);

        const result = await BidModel.create({
            user_id: userId,
            sponsorship_offer_id: sponsorshipOfferId,
            bid_amount: bidAmount,
            bid_date: bidDate,
        });

        logger.info(`Bid placed: bid_id=${result.insertId}, user_id=${userId}, amount=${bidAmount}, date=${bidDate}`);

        return {
            id: result.insertId,
            user_id: userId,
            sponsorship_offer_id: sponsorshipOfferId,
            bid_amount: bidAmount,
            bid_date: bidDate,
            bid_status: "active",
        };
    }

    static async updateBid(bidId, userId, newAmount, sponsorshipOfferId = null) {
        const bid = await BidModel.findByIdAndUser(bidId, userId);
        if (!bid) {
            const error = new Error("Bid not found.");
            error.code = "BID_NOT_FOUND";
            error.status = 404;
            throw error;
        }

        const bidDate = this.getDateKeyFromValue(bid.bid_date);
        if (!bidDate) {
            const error = new Error("Stored bid date is invalid.");
            error.code = "INVALID_BID_DATE";
            error.status = 400;
            throw error;
        }

        this.enforceTomorrowOnlyBidDate(bidDate);
        this.enforceTomorrowBidCutoff(bidDate);

        if (bid.is_cancelled) {
            const error = new Error("Cannot update a cancelled bid.");
            error.code = "BID_CANCELLED";
            error.status = 400;
            throw error;
        }

        if (bid.bid_status !== "active") {
            const error = new Error("Cannot update a resolved bid.");
            error.code = "BID_RESOLVED";
            error.status = 400;
            throw error;
        }

        const currentAmount = parseFloat(bid.bid_amount);
        if (newAmount <= currentAmount) {
            const error = new Error(
                `New bid amount (${newAmount.toFixed(2)}) must be greater than current amount (${currentAmount.toFixed(2)}).`
            );
            error.code = "BID_DECREASE_NOT_ALLOWED";
            error.status = 400;
            throw error;
        }

        const balance = await BidModel.getAvailableSponsorshipBalance(userId, bidId);
        const availableBalance = balance.available_balance;
        if (newAmount > availableBalance) {
            const error = new Error(
                `Insufficient sponsorship funds. Available: ${availableBalance.toFixed(2)}, Requested: ${newAmount.toFixed(2)}`
            );
            error.code = "INSUFFICIENT_FUNDS";
            error.status = 400;
            throw error;
        }

        const nextSponsorshipOfferId = sponsorshipOfferId ?? bid.sponsorship_offer_id ?? null;
        await this.validateSpecificSponsorshipOffer(userId, nextSponsorshipOfferId, newAmount);

        await BidModel.updateAmount(bidId, newAmount);
        if (nextSponsorshipOfferId !== bid.sponsorship_offer_id) {
            await BidModel.updateSponsorshipOffer(bidId, nextSponsorshipOfferId);
        }

        logger.info(`Bid updated: bid_id=${bidId}, old_amount=${currentAmount}, new_amount=${newAmount}`);

        return {
            id: bidId,
            sponsorship_offer_id: nextSponsorshipOfferId,
            bid_amount: newAmount,
            previous_amount: currentAmount,
            bid_status: "active",
        };
    }

    static async cancelBid(bidId, userId) {
        const bid = await BidModel.findByIdAndUser(bidId, userId);

        if (!bid) {
            const error = new Error("Bid not found.");
            error.code = "BID_NOT_FOUND";
            error.status = 404;
            throw error;
        }

        if (bid.is_cancelled) {
            const error = new Error("This bid has already been cancelled.");
            error.code = "BID_ALREADY_CANCELLED";
            error.status = 400;
            throw error;
        }

        if (bid.bid_status !== "active") {
            const error = new Error("Cannot cancel a resolved bid.");
            error.code = "BID_RESOLVED";
            error.status = 400;
            throw error;
        }

        await BidModel.cancel(bidId);

        logger.info(`Bid cancelled: bid_id=${bidId}, user_id=${userId}`);

        return {
            id: bidId,
            is_cancelled: true,
            bid_date: bid.bid_date,
        };
    }

    static async getBidStatus(userId) {
        const userBid = await BidModel.findLatestActiveBid(userId);
        if (!userBid) {
            const error = new Error("No active bid found.");
            error.code = "NO_ACTIVE_BID";
            error.status = 404;
            throw error;
        }

        const highestAmount = await BidModel.getHighestBidForDate(userBid.bid_date);
        const isWinning = parseFloat(userBid.bid_amount) >= highestAmount;

        return {
            bid_id: userBid.id,
            bid_date: userBid.bid_date,
            your_bid_amount: parseFloat(userBid.bid_amount),
            is_winning: isWinning,
        };
    }

    static async getBidHistory(userId, page = 1, limit = 10) {
        const offset = (page - 1) * limit;
        const [bids, total] = await Promise.all([
            BidModel.findHistoryByUser(userId, limit, offset),
            BidModel.countByUser(userId),
        ]);

        return {
            bids,
            pagination: {
                page,
                limit,
                total,
                totalPages: Math.ceil(total / limit),
            },
        };
    }

    static async getMonthlyLimit(userId) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT, 10) || 3;
        const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT, 10) || 4;
        const record = await BidModel.getMonthlyCount(userId, year, month);
        const count = record ? record.count : 0;
        const attendedEvent = record ? Boolean(record.attended_event) : false;
        const maxAllowed = attendedEvent ? eventLimit : normalLimit;

        return {
            featured_count: count,
            max_allowed: maxAllowed,
            remaining: Math.max(0, maxAllowed - count),
            attended_event: attendedEvent,
            year,
            month,
        };
    }

    static async markEventAttendance(userId, eventName = null, eventDate = null) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const normalizedEventName = typeof eventName === "string" && eventName.trim() !== ""
            ? eventName.trim()
            : "University Alumni Event";
        const normalizedEventDate = typeof eventDate === "string" && eventDate.trim() !== ""
            ? eventDate
            : new Date().toISOString().split("T")[0];

        const profileId = await AlumniEventModel.findProfileIdByUserId(userId);
        if (!profileId) {
            const error = new Error("Alumni profile not found for event attendance.");
            error.code = "PROFILE_NOT_FOUND";
            error.status = 404;
            throw error;
        }

        await AlumniEventModel.create(profileId, normalizedEventName, normalizedEventDate);
        await pool.execute(
            `INSERT INTO monthly_feature_counts (user_id, year, month, count, attended_event)
             VALUES (?, ?, ?, 0, true)
             ON DUPLICATE KEY UPDATE attended_event = true`,
            [userId, year, month]
        );

        logger.info(
            `Event attendance marked: user_id=${userId}, profile_id=${profileId}, year=${year}, month=${month}, event_date=${normalizedEventDate}`
        );

        const monthlyLimit = await this.getMonthlyLimit(userId);
        return {
            ...monthlyLimit,
            event_name: normalizedEventName,
            event_date: normalizedEventDate,
        };
    }

    static async getAvailableBalance(userId) {
        return BidModel.getAvailableSponsorshipBalance(userId);
    }
}

export default BiddingService;