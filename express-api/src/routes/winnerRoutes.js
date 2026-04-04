import express from 'express';
import WinnerController from "../controllers/winnerController.js";
import {authenticateAny, authenticateInternal} from "../middleware/authMiddleware.js";
import {validatePagination} from "../middleware/inputValidator.js";

const router = express.Router();

/**
 * @swagger
 * /api/v1/winners/today:
 *   get:
 *     summary: Get today's winner with bid details
 *     description: >
 *       Returns today's winner including full profile information AND
 *       bid amount. This data is for internal use only (admin dashboard).
 *       The public API endpoint at /api/v1/public/featured-alumni/today
 *       returns the same profile without bid details.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Today's winner details with bid amount
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   type: object
 *                   properties:
 *                     id:
 *                       type: integer
 *                       example: 1
 *                     featured_date:
 *                       type: string
 *                       format: date
 *                       example: "2026-04-04"
 *                     user_id:
 *                       type: integer
 *                       example: 5
 *                     bid_id:
 *                       type: integer
 *                       example: 12
 *                     bid_amount:
 *                       type: number
 *                       example: 250.00
 *                     email:
 *                       type: string
 *                       example: "alumni@eastminster.ac.uk"
 *                     bio:
 *                       type: string
 *                     linkedin_url:
 *                       type: string
 *                     profile_image_url:
 *                       type: string
 *       404:
 *         description: No winner selected for today
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/Error'
 *       401:
 *         description: Authentication required
 */
router.get(
    '/today',
    authenticateAny,
    WinnerController.getTodayWinner
);

/**
 * @swagger
 * /api/v1/winners/history:
 *   get:
 *     summary: Get winners history with bid amounts
 *     description: >
 *       Returns a paginated list of past winners INCLUDING their bid
 *       amounts. This data is for internal use only. Use the public
 *       endpoint at /api/v1/public/featured-alumni/history for
 *       data without bid amounts.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *           default: 1
 *           minimum: 1
 *         description: Page number (1-based)
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *           default: 10
 *           minimum: 1
 *           maximum: 100
 *         description: Results per page
 *     responses:
 *       200:
 *         description: Paginated winners history with bid amounts
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   type: object
 *                   properties:
 *                     winners:
 *                       type: array
 *                       items:
 *                         type: object
 *                         properties:
 *                           featured_date:
 *                             type: string
 *                             format: date
 *                           user_id:
 *                             type: integer
 *                           bid_amount:
 *                             type: number
 *                           email:
 *                             type: string
 *                     pagination:
 *                       type: object
 *                       properties:
 *                         page:
 *                           type: integer
 *                         limit:
 *                           type: integer
 *                         total:
 *                           type: integer
 *                         total_pages:
 *                           type: integer
 *       401:
 *         description: Authentication required
 */
router.get(
    '/history',
    authenticateAny,
    validatePagination,
    WinnerController.getWinnerHistory
);

/**
 * @swagger
 * /api/v1/winners/check:
 *   get:
 *     summary: Check if a winner exists for a specific date
 *     description: >
 *       Utility endpoint to check whether the cron job has run and
 *       selected a winner for a given date. Used by the admin dashboard
 *       to display cron job status.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: date
 *         required: true
 *         schema:
 *           type: string
 *           format: date
 *           example: "2026-04-05"
 *         description: Date to check (YYYY-MM-DD format)
 *     responses:
 *       200:
 *         description: Winner existence check result
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   type: object
 *                   properties:
 *                     date:
 *                       type: string
 *                       format: date
 *                     winner_exists:
 *                       type: boolean
 *       400:
 *         description: Missing or invalid date parameter
 *       401:
 *         description: Authentication required
 */
router.get(
    '/check',
    authenticateInternal,
    WinnerController.checkWinnerExists
);

/**
 * @swagger
 * /api/v1/winners/trigger:
 *   post:
 *     summary: Manually trigger winner selection (admin only)
 *     description: >
 *       Manually triggers the daily winner selection algorithm for
 *       tomorrow's date. This is used for testing or if the automated
 *       cron job failed to run.
 *
 *       Includes a duplicate check — if a winner has already been
 *       selected for tomorrow, the request returns a message instead
 *       of selecting another winner.
 *
 *       This endpoint should only be used by administrators.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *     responses:
 *       201:
 *         description: Winner selected successfully
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 success:
 *                   type: boolean
 *                   example: true
 *                 data:
 *                   type: object
 *                   properties:
 *                     winner_user_id:
 *                       type: integer
 *                       example: 5
 *                     bid_id:
 *                       type: integer
 *                       example: 12
 *                     bid_amount:
 *                       type: number
 *                       example: 250.00
 *                     featured_date:
 *                       type: string
 *                       format: date
 *                       example: "2026-04-05"
 *                     losers_count:
 *                       type: integer
 *                       example: 3
 *                     sponsorships_paid:
 *                       type: integer
 *                       example: 2
 *                 message:
 *                   type: string
 *                   example: "Winner selected for 2026-04-05: user_id=5"
 *       200:
 *         description: No winner selected (already exists or no eligible bids)
 *       401:
 *         description: Authentication required
 *       500:
 *         description: Winner selection failed
 */
router.post(
    '/trigger',
    authenticateInternal,
    WinnerController.triggerManualSelection
);

export default router;





