import SponsorshipService from '../services/SponsorshipService.js';
import { sendSuccess, sendError } from '../utils/responseHelper.js';
import { logger } from '../config/logger.js';

class SponsorshipController {
    static async createSponsor(req, res) {
        try {
            const { sponsor_name, sponsor_type, website_url } = req.body;

            const result = await SponsorshipService.createSponsor({
                sponsor_name,
                sponsor_type,
                website_url,
            });

            return sendSuccess(res, result, 'Sponsor created successfully.', 201);

        } catch (error) {
            logger.error('SponsorshipController.createSponsor error:', {
                message: error.message,
                code: error.code,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'SPONSOR_CREATE_ERROR', 'Failed to create sponsor.', 500);
        }
    }
    static async listSponsors(req, res) {
        try {
            const sponsorType = req.query.type || null;

            const result = await SponsorshipService.getAllSponsors(sponsorType);

            return sendSuccess(res, result, 'Sponsors retrieved.');

        } catch (error) {
            logger.error('SponsorshipController.listSponsors error:', {
                message: error.message,
            });

            return sendError(res, 'SPONSOR_LIST_ERROR', 'Failed to list sponsors.', 500);
        }
    }
    static async createOffer(req, res) {
        try {
            const { sponsor_id, user_id, sponsorable_id, sponsorable_type, offer_amount } = req.body;

            const result = await SponsorshipService.createOffer({
                sponsor_id,
                user_id,
                sponsorable_id,
                sponsorable_type,
                offer_amount: parseFloat(offer_amount),
            });

            return sendSuccess(res, result, 'Sponsorship offer created.', 201);

        } catch (error) {
            logger.error('SponsorshipController.createOffer error:', {
                message: error.message,
                code: error.code,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'OFFER_CREATE_ERROR', 'Failed to create sponsorship offer.', 500);
        }
    }
    static async getOffers(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || req.internalUserId;
            const status = req.query.status || null;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            let result;
            if (status) {
                result = await SponsorshipService.getOffersByStatus(userId, status);
            } else {
                result = await SponsorshipService.getOffers(userId);
            }

            return sendSuccess(res, result, 'Sponsorship offers retrieved.');

        } catch (error) {
            logger.error('SponsorshipController.getOffers error:', {
                message: error.message,
            });

            return sendError(res, 'OFFER_LIST_ERROR', 'Failed to retrieve offers.', 500);
        }
    }
    static async respondToOffer(req, res) {
        try {
            const offerId = parseInt(req.params.id);
            const { action, user_id } = req.body;
            const userId = user_id || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await SponsorshipService.respondToOffer(offerId, userId, action);

            return sendSuccess(res, result, `Offer ${action}ed successfully.`);

        } catch (error) {
            logger.error('SponsorshipController.respondToOffer error:', {
                message: error.message,
                code: error.code,
                offerId: req.params.id,
            });

            if (error.code) {
                return sendError(res, error.code, error.message, error.status || 400);
            }

            return sendError(res, 'OFFER_RESPONSE_ERROR', 'Failed to respond to offer.', 500);
        }
    }
    static async getBalance(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await SponsorshipService.getBalance(userId);

            return sendSuccess(res, result, 'Sponsorship balance retrieved.');

        } catch (error) {
            logger.error('SponsorshipController.getBalance error:', {
                message: error.message,
            });

            return sendError(res, 'BALANCE_ERROR', 'Failed to retrieve balance.', 500);
        }
    }
    static async getOfferSummary(req, res) {
        try {
            const userId = parseInt(req.query.user_id) || req.internalUserId;

            if (!userId) {
                return sendError(res, 'MISSING_USER_ID', 'User ID is required.', 400);
            }

            const result = await SponsorshipService.getOfferSummary(userId);

            return sendSuccess(res, result, 'Offer summary retrieved.');

        } catch (error) {
            logger.error('SponsorshipController.getOfferSummary error:', {
                message: error.message,
            });

            return sendError(res, 'SUMMARY_ERROR', 'Failed to retrieve offer summary.', 500);
        }
    }
}

export default SponsorshipController;