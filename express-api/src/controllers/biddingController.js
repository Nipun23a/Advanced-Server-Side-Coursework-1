import BiddingService from "../services/BiddingService.js";
import {sendError, sendSuccess} from "../utils/responseHelper.js";
import {logger} from "../config/logger.js";

class BiddingController {
    static async placeBid(req, res) {
        try {
            const { bid_amount, bid_date, user_id } = req.body;
            const userId = user_id || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }
            const result = await BiddingService.placeBid(userId, parseFloat(bid_amount), bid_date);

            return sendSuccess(res, result, 'Bid placed successfully.', 201);

        } catch (error) {
            logger.error('BidController.placeBid error:', {
                message: error.message,
                code: error.code,
                ip: req.ip,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'BID_PLACEMENT_ERROR', 'Failed to place bid.', 500);
        }
    }

    static async updateBid(req, res) {
        try {
            const bidId = parseInt(req.params.id);
            const { bid_amount, user_id } = req.body;
            const userId = user_id || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.updateBid(bidId, userId, parseFloat(bid_amount));

            return sendSuccess(res, result, 'Bid updated successfully.');

        } catch (error) {
            logger.error('BidController.updateBid error:', {
                message: error.message,
                code: error.code,
                bidId: req.params.id,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'BID_UPDATE_ERROR', 'Failed to update bid.', 500);
        }
    }

    static async cancelBid(req, res) {
        try {
            const bidId = parseInt(req.params.id);
            const userId = req.body?.user_id || parseInt(req.query.user_id) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.cancelBid(bidId, userId);

            return sendSuccess(res, result, 'Bid cancelled successfully.');

        } catch (error) {
            logger.error('BidController.cancelBid error:', {
                message: error.message,
                code: error.code,
                bidId: req.params.id,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'BID_CANCEL_ERROR', 'Failed to cancel bid.', 500);
        }
    }

    static async getBidStatus(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || (req.apiKey ? req.apiKey.userId : null) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.getBidStatus(userId);

            return sendSuccess(res, result, 'Bid status retrieved.');

        } catch (error) {
            logger.error('BidController.getBidStatus error:', {
                message: error.message,
                code: error.code,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'BID_STATUS_ERROR', 'Failed to get bid status.', 500);
        }
    }

    static async getBidHistory(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || (req.apiKey ? req.apiKey.userId : null) || req.internalUserId;
            const page = parseInt(req.query.page) || 1;
            const limit = Math.min(parseInt(req.query.limit) || 10, 100);

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.getBidHistory(userId, page, limit);

            return sendSuccess(res, result, 'Bid history retrieved.');

        } catch (error) {
            logger.error('BidController.getBidHistory error:', {
                message: error.message,
                stack: error.stack,
            });

            return sendError(res, 'BID_HISTORY_ERROR', 'Failed to get bid history.', 500);
        }
    }

    static async getMonthlyLimit(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || (req.apiKey ? req.apiKey.userId : null) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.getMonthlyLimit(userId);

            return sendSuccess(res, result, 'Monthly limit status retrieved.');

        } catch (error) {
            logger.error('BidController.getMonthlyLimit error:', {
                message: error.message,
                stack: error.stack,
            });

            return sendError(res, 'MONTHLY_LIMIT_ERROR', 'Failed to get monthly limit.', 500);
        }
    }

    static async markEventAttendance(req, res) {
        try {
            const userId = parseInt(req.body?.user_id) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.markEventAttendance(userId);

            return sendSuccess(res, result, 'Event attendance recorded successfully.');

        } catch (error) {
            logger.error('BidController.markEventAttendance error:', {
                message: error.message,
                code: error.code,
                stack: error.stack,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'EVENT_ATTENDANCE_ERROR', 'Failed to record event attendance.', 500);
        }
    }

    static async getAvailableBalance(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || (req.apiKey ? req.apiKey.userId : null) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await BiddingService.getAvailableBalance(userId);

            return sendSuccess(res, result, 'Available balance retrieved.');

        } catch (error) {
            logger.error('BidController.getAvailableBalance error:', {
                message: error.message,
                stack: error.stack,
            });

            return sendError(res, 'BALANCE_ERROR', 'Failed to get available balance.', 500);
        }
    }
}
export default BiddingController;
