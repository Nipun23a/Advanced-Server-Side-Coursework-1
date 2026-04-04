import BidModel from "../models/BidModel.js";
import {logger} from "../config/logger.js";

class BiddingService {
    static async placeBid(userId, bidAmount, bidDate) {
        const existingBid = await BidModel.findActiveByUserAndDate(userId, bidDate);

        if (existingBid) {
            const error = new Error('You already have an active bid for this date.');
            error.code = 'DUPLICATE_BID';
            error.status = 409;
            throw error;
        }
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;

        const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT) || 3;
        const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT) || 4;

        const monthlyRecord = await BidModel.getMonthlyCount(userId, year, month);

        if (monthlyRecord) {
            const currentCount = monthlyRecord.count;
            const attendedEvent = Boolean(monthlyRecord.attended_event);
            const maxAllowed = attendedEvent ? eventLimit : normalLimit;

            if (currentCount >= maxAllowed) {
                const error = new Error(`Monthly feature limit reached (${currentCount}/${maxAllowed}).`);
                error.code = 'MONTHLY_LIMIT_REACHED';
                error.status = 403;
                throw error;
            }
        }
        const availableBalance = await BidModel.getAvailableSponsorshipBalance(userId);

        if (bidAmount > availableBalance) {
            const error = new Error(
                `Insufficient sponsorship funds. Available: ${availableBalance.toFixed(2)}, Requested: ${bidAmount.toFixed(2)}`
            );
            error.code = 'INSUFFICIENT_FUNDS';
            error.status = 400;
            throw error;
        }

        // ---- All rules passed — place the bid ----
        const result = await BidModel.create({
            user_id: userId,
            bid_amount: bidAmount,
            bid_date: bidDate,
            sponsorship_total: availableBalance,
        });

        logger.info(`Bid placed: bid_id=${result.insertId}, user_id=${userId}, amount=${bidAmount}, date=${bidDate}`);

        return {
            id: result.insertId,
            user_id: userId,
            bid_amount: bidAmount,
            bid_date: bidDate,
            bid_status: 'active',
            sponsorship_total: availableBalance,
        };
    }

    static async updateBid(bidId, userId, newAmount) {
        const bid = await BidModel.findByIdAndUser(bidId, userId);
        if (!bid) {
            const error = new Error('Bid not found.');
            error.code = 'BID_NOT_FOUND';
            error.status = 404;
            throw error;
        }
        if (bid.is_cancelled) {
            const error = new Error('Cannot update a cancelled bid.');
            error.code = 'BID_CANCELLED';
            error.status = 400;
            throw error;
        }
        if (bid.bid_status !== 'active') {
            const error = new Error('Cannot update a resolved bid.');
            error.code = 'BID_RESOLVED';
            error.status = 400;
            throw error;
        }
        const currentAmount = parseFloat(bid.bid_amount);
        if (newAmount <= currentAmount) {
            const error = new Error(
                `New bid amount (${newAmount.toFixed(2)}) must be greater than current amount (${currentAmount.toFixed(2)}).`
            );
            error.code = 'BID_DECREASE_NOT_ALLOWED';
            error.status = 400;
            throw error;
        }
        const availableBalance = await BidModel.getAvailableSponsorshipBalance(userId);
        if (newAmount > availableBalance) {
            const error = new Error(
                `Insufficient sponsorship funds. Available: ${availableBalance.toFixed(2)}, Requested: ${newAmount.toFixed(2)}`
            );
            error.code = 'INSUFFICIENT_FUNDS';
            error.status = 400;
            throw error;
        }
        await BidModel.updateAmount(bidId, newAmount);
        logger.info(`Bid updated: bid_id=${bidId}, old_amount=${currentAmount}, new_amount=${newAmount}`);
        return {
            id: bidId,
            bid_amount: newAmount,
            previous_amount: currentAmount,
            bid_status: 'active',
        };
    }

    static async cancelBid(bidId, userId) {

        const bid = await BidModel.findByIdAndUser(bidId, userId);

        if (!bid) {
            const error = new Error('Bid not found.');
            error.code = 'BID_NOT_FOUND';
            error.status = 404;
            throw error;
        }

        if (bid.is_cancelled) {
            const error = new Error('This bid has already been cancelled.');
            error.code = 'BID_ALREADY_CANCELLED';
            error.status = 400;
            throw error;
        }

        if (bid.bid_status !== 'active') {
            const error = new Error('Cannot cancel a resolved bid.');
            error.code = 'BID_RESOLVED';
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
            const error = new Error('No active bid found.');
            error.code = 'NO_ACTIVE_BID';
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

        const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT) || 3;
        const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT) || 4;

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

    static async getAvailableBalance(userId) {
        const balance = await BidModel.getAvailableSponsorshipBalance(userId);
        return {
            available_balance: balance,
        };
    }

}

export default BiddingService;