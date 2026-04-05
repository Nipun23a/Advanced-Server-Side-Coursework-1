import {pool} from "../config/database.js";
import {logger} from "../config/logger.js";
import BidModel from "../models/BidModel.js";
import FeatureAlumniModel from "../models/FeatureAlumniModel.js";
import MonthlyFeatureCountModel from "../models/MonthlyFeatureCountModel.js";
import SponsorshipOfferModel from "../models/SponsorshipOfferModel.js";
import FeaturedAlumniModel from "../models/FeatureAlumniModel.js";
import NotificationService from "./NotificationService.js";

class WinnerService {

    static async selectDailyWinner(){
        const connection = await pool.getConnection();
        try{
            await connection.beginTransaction();
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];

            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth() + 1;

            const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT) || 3;
            const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT) || 4;

            const candidates = await FeaturedAlumniModel.findCandidateBids(tomorrowStr, connection);

            if (candidates.length === 0) {
                logger.info(`Winner selection: No bids found for ${tomorrowStr}. No winner selected.`);
                await connection.commit();
                connection.release();
                return null;
            }

            logger.info(`Winner selection: ${candidates.length} candidate(s) found for ${tomorrowStr}`);

            let winner = null;

            for (const candidate of candidates) {
                const monthlyRecord = await FeaturedAlumniModel.getMonthlyCount(
                    candidate.user_id, year, month, connection
                );

                const currentCount = monthlyRecord ? monthlyRecord.count : 0;
                const attendedEvent = monthlyRecord ? Boolean(monthlyRecord.attended_event) : false;

                // Business rule: 3 features/month normally, 4 with event attendance
                const maxAllowed = attendedEvent ? eventLimit : normalLimit;

                if (currentCount < maxAllowed) {
                    winner = candidate;
                    logger.info(
                        `Winner selection: User ${candidate.user_id} eligible ` +
                        `(featured ${currentCount}/${maxAllowed} times this month)`
                    );
                    break;
                }

                logger.info(
                    `Winner selection: User ${candidate.user_id} reached monthly limit ` +
                    `(${currentCount}/${maxAllowed}). Skipping to next candidate.`
                );
            }
            if (!winner) {
                logger.info(
                    `Winner selection: All ${candidates.length} bidder(s) for ${tomorrowStr} ` +
                    `have reached their monthly feature limits. No winner selected.`
                );
                await connection.commit();
                connection.release();
                return null;
            }
            const losingCandidates = candidates.filter((candidate) => candidate.id !== winner.id);

            await FeaturedAlumniModel.markBidAsWon(winner.id, connection);

            const losersCount = await FeaturedAlumniModel.markOtherBidsAsLost(
                tomorrowStr, winner.id, connection
            );
            logger.info(`Winner selection: ${losersCount} bid(s) marked as lost`);

            await FeaturedAlumniModel.create(winner.user_id, winner.id, tomorrowStr, connection);
            await FeaturedAlumniModel.incrementMonthlyCount(winner.user_id, year, month, connection);
            const sponsorshipsPaid = await FeaturedAlumniModel.markSponsorshipsPaid(
                winner.user_id, connection
            );
            logger.info(`Winner selection: ${sponsorshipsPaid} sponsorship offer(s) marked as paid`);
            await connection.commit();

            let notificationSummary = null;
            try {
                notificationSummary = await NotificationService.sendWinnerSelectionNotifications({
                    winner,
                    losers: losingCandidates,
                    featuredDate: tomorrowStr,
                });
            } catch (notificationError) {
                logger.error('Winner selection: notification send failed after commit', {
                    message: notificationError.message,
                    stack: notificationError.stack,
                });
            }

            logger.info(
                `Winner selection: COMPLETE for ${tomorrowStr} — ` +
                `user_id=${winner.user_id}, bid_id=${winner.id}, amount=${winner.bid_amount}`
            );

            return {
                winner_user_id: winner.user_id,
                bid_id: winner.id,
                bid_amount: parseFloat(winner.bid_amount),
                featured_date: tomorrowStr,
                losers_count: losersCount,
                sponsorships_paid: sponsorshipsPaid,
                notifications: notificationSummary,
            };
        }catch (error){
            await connection.rollback();
            logger.error('Winner selection: FAILED — transaction rolled back:', error);
            throw error;
        }finally {
            connection.release();
        }
    }

    static async getTodayFeaturedProfile(){
        const today = new Date().toISOString().split('T')[0];
        const alumni = await FeaturedAlumniModel.findByDate(today);
        if (!alumni) {
            logger.info(`No featured alumni found for today (${today})`);
            return null;
        }
        const credentials = await FeaturedAlumniModel.fetchAllCredentials(alumni.user_id);
        return {
            featured_date: alumni.featured_at,
            email: alumni.email,
            bio: alumni.bio,
            linkedin_url: alumni.linkedin_url,
            profile_image_url: alumni.profile_image_url,
            ...credentials,
        };
    }

    static async getTodayWinner() {
        const today = new Date().toISOString().split('T')[0];
        return await FeaturedAlumniModel.findByDateWithBidDetails(today);
    }

    static async getFeaturedHistory(page = 1, limit = 10) {
        const offset = (page - 1) * limit;
        const [featured, total] = await Promise.all([
            FeaturedAlumniModel.findHistory(limit, offset),
            FeaturedAlumniModel.countAll(),
        ]);

        return {
            featured,
            pagination: {
                page,
                limit,
                total,
                totalPages: Math.ceil(total / limit),
            },
        };
    }

    static async getWinnerHistory(page = 1, limit = 10) {
        const offset = (page - 1) * limit;
        const [winners, total] = await Promise.all([
            FeaturedAlumniModel.findWinnersHistory(limit, offset),
            FeaturedAlumniModel.countAll(),
        ]);
        return {
            winners,
            pagination: {
                page,
                limit,
                total,
                total_pages: Math.ceil(total / limit),
            },
        };
    }

    static async hasWinnerForDate(date) {
        return await FeaturedAlumniModel.existsByDate(date);
    }

    static async triggerManualSelection() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dateStr = tomorrow.toISOString().split('T')[0];
        const alreadySelected = await this.hasWinnerForDate(dateStr);
        if (alreadySelected) {
            logger.warn(`Manual selection: Winner already exists for ${dateStr}`);
            return {
                result: null,
                message: `A winner has already been selected for ${dateStr}.`,
            };
        }

        const result = await this.selectDailyWinner();

        return {
            result,
            message: result
                ? `Winner selected for ${dateStr}: user_id=${result.winner_user_id}`
                : `No eligible bids found for ${dateStr}.`,
        };
    }




}

export default WinnerService;
