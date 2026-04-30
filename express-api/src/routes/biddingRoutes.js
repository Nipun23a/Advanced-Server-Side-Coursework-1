import express from 'express';
import { authenticateInternal } from '../middleware/authMiddleware.js';
import { biddingLimiter } from '../middleware/rateLimiter.js';
import {
    validatePlaceBid,
    validateUpdateBid,
    validateCancelBid,
    validatePagination,
} from '../middleware/inputValidator.js';
import BiddingController from "../controllers/biddingController.js";

const router = express.Router();

// ============================================================================
// WRITE OPERATIONS — Internal auth + bidding rate limiter
// ============================================================================

/**
 * @swagger
 * /api/v1/bids:
 *   post:
 *     summary: Place a new bid
 *     description: >
 *       Place a blind bid for tomorrow's featured alumni slot.
 *       The bid amount must not exceed the user's available sponsorship funds.
 *       Only one bid per user per day is allowed.
 *       Monthly feature limit (3/month, or 4 with event attendance) is enforced.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [bid_amount, bid_date, user_id]
 *             properties:
 *               bid_amount:
 *                 type: number
 *                 format: decimal
 *                 example: 250.00
 *                 description: Amount to bid (max 2 decimal places)
 *               bid_date:
 *                 type: string
 *                 format: date
 *                 example: "2026-04-05"
 *                 description: Date bidding for (must be tomorrow or later)
 *               user_id:
 *                 type: integer
 *                 example: 5
 *     responses:
 *       201:
 *         description: Bid placed successfully
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 id: 1
 *                 user_id: 5
 *                 bid_amount: 250.00
 *                 bid_date: "2026-04-05"
 *                 bid_status: "active"
 *                 sponsorship_total: 500.00
 *       400:
 *         description: Validation error or insufficient funds
 *       403:
 *         description: Monthly feature limit reached
 *       409:
 *         description: Duplicate bid for this date
 *       401:
 *         description: Authentication required
 *       429:
 *         description: Rate limit exceeded
 */
router.post(
    '/',
    authenticateInternal,
    biddingLimiter,
    validatePlaceBid,
    BiddingController.placeBid
);

/**
 * @swagger
 * /api/v1/bids/{id}:
 *   put:
 *     summary: Update a bid (increase only)
 *     description: >
 *       Update an existing bid amount. The new amount must be strictly
 *       greater than the current amount (decrease is not allowed).
 *       Cannot update cancelled or resolved (won/lost) bids.
 *       New amount must not exceed available sponsorship funds.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [bid_amount]
 *             properties:
 *               bid_amount:
 *                 type: number
 *                 example: 300.00
 *               user_id:
 *                 type: integer
 *                 example: 5
 *     responses:
 *       200:
 *         description: Bid updated successfully
 *       400:
 *         description: New amount must exceed current, or insufficient funds
 *       404:
 *         description: Bid not found
 *       401:
 *         description: Authentication required
 *       429:
 *         description: Rate limit exceeded
 */
router.put(
    '/:id',
    authenticateInternal,
    biddingLimiter,
    validateUpdateBid,
    BiddingController.updateBid
);

/**
 * @swagger
 * /api/v1/bids/{id}:
 *   delete:
 *     summary: Cancel a bid
 *     description: >
 *       Cancel an active bid. Sets is_cancelled to true.
 *       Cannot cancel bids that are already cancelled or resolved (won/lost).
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [user_id]
 *             properties:
 *               user_id:
 *                 type: integer
 *                 example: 1
 *     responses:
 *       200:
 *         description: Bid cancelled successfully
 *       400:
 *         description: Bid already cancelled or resolved
 *       404:
 *         description: Bid not found
 *       401:
 *         description: Authentication required
 */
router.delete(
    '/:id',
    authenticateInternal,
    validateCancelBid,
    BiddingController.cancelBid
);

/**
 * @swagger
 * /api/v1/bids/event-attendance:
 *   post:
 *     summary: Mark alumni event attendance for the current month
 *     description: >
 *       Records that the user attended a university alumni event this month.
 *       This enables the higher monthly feature limit of 4 instead of 3.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [user_id]
 *             properties:
 *               user_id:
 *                 type: integer
 *                 example: 5
 *     responses:
 *       200:
 *         description: Event attendance recorded successfully
 *       401:
 *         description: Authentication required
 */
router.post(
    '/event-attendance',
    authenticateInternal,
    BiddingController.markEventAttendance
);

// ============================================================================
// READ OPERATIONS — Any auth (internal or bearer)
// ============================================================================

/**
 * @swagger
 * /api/v1/bids/status:
 *   get:
 *     summary: Get bid status (winning/not winning)
 *     description: >
 *       Returns whether the user's active bid is currently the highest
 *       for the target date. Does NOT reveal the actual highest bid
 *       amount — only indicates winning or not winning (blind bidding).
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Bid status
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 bid_id: 1
 *                 bid_date: "2026-04-05"
 *                 your_bid_amount: 250.00
 *                 is_winning: true
 *       404:
 *         description: No active bid found
 *       401:
 *         description: Authentication required
 */
router.get(
    '/status',
    authenticateInternal,
    BiddingController.getBidStatus
);

/**
 * @swagger
 * /api/v1/bids/history:
 *   get:
 *     summary: View bidding history
 *     description: Paginated bidding history for the user.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *           default: 1
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *           default: 10
 *           maximum: 100
 *     responses:
 *       200:
 *         description: Paginated bid history
 *       401:
 *         description: Authentication required
 */
router.get(
    '/history',
    authenticateInternal,
    validatePagination,
    BiddingController.getBidHistory
);

/**
 * @swagger
 * /api/v1/bids/monthly-limit:
 *   get:
 *     summary: View monthly feature limit status
 *     description: >
 *       Returns how many times the user has been featured this month,
 *       the maximum allowed (3 or 4 with event), remaining slots,
 *       and whether they attended an event.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Monthly limit status
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 featured_count: 2
 *                 max_allowed: 3
 *                 remaining: 1
 *                 attended_event: false
 *                 year: 2026
 *                 month: 4
 *       401:
 *         description: Authentication required
 */
router.get(
    '/monthly-limit',
    authenticateInternal,
    BiddingController.getMonthlyLimit
);

/**
 * @swagger
 * /api/v1/bids/balance:
 *   get:
 *     summary: View available sponsorship balance
 *     description: >
 *       Returns the total sponsorship funds available for bidding.
 *       Calculated as the sum of all accepted, unpaid sponsorship offers.
 *     tags: [Bidding]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Available balance
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 available_balance: 500.00
 *       401:
 *         description: Authentication required
 */
router.get(
    '/balance',
    authenticateInternal,
    BiddingController.getAvailableBalance
);

export default router;
