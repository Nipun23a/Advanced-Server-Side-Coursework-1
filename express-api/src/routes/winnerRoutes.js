import express from 'express';
import { authenticateAny, authenticateInternal } from '../middleware/authMiddleware.js';
import { validatePagination } from '../middleware/inputValidator.js';
import WinnerController from '../controllers/WinnerController.js';

const router = express.Router();

/**
 * @swagger
 * /api/v1/winners/today:
 *   get:
 *     summary: Get today's winner with bid details (internal)
 *     description: >
 *       Returns today's winner including profile AND bid amount.
 *       For internal use only (admin dashboard). The public endpoint
 *       at /api/v1/public/featured-alumni/today returns the same
 *       profile without bid details.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Today's winner with bid details
 *       404:
 *         description: No winner selected for today
 *       401:
 *         description: Authentication required
 */
router.get('/today', authenticateAny, WinnerController.getTodayWinner);

/**
 * @swagger
 * /api/v1/winners/history:
 *   get:
 *     summary: Get winners history with bid amounts (internal)
 *     description: >
 *       Paginated list of past winners INCLUDING bid amounts.
 *       Not available through the public API.
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
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *           default: 10
 *           maximum: 100
 *     responses:
 *       200:
 *         description: Paginated winners history
 *       401:
 *         description: Authentication required
 */
router.get('/history', authenticateAny, validatePagination, WinnerController.getWinnersHistory);

/**
 * @swagger
 * /api/v1/winners/check:
 *   get:
 *     summary: Check if a winner exists for a specific date
 *     description: >
 *       Utility endpoint for the admin dashboard to verify whether
 *       the cron job has run and selected a winner.
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
 *     responses:
 *       200:
 *         description: Winner existence check result
 *       400:
 *         description: Missing or invalid date
 *       401:
 *         description: Authentication required
 */
router.get('/check', authenticateInternal, WinnerController.checkWinnerExists);

/**
 * @swagger
 * /api/v1/winners/trigger:
 *   post:
 *     summary: Manually trigger winner selection (admin only)
 *     description: >
 *       Triggers the daily winner selection algorithm for tomorrow.
 *       Includes duplicate check — returns a message if winner
 *       already exists. Used for testing or if cron missed.
 *     tags: [Winners (Internal)]
 *     security:
 *       - InternalAuth: []
 *     responses:
 *       201:
 *         description: Winner selected successfully
 *       200:
 *         description: No winner selected (already exists or no eligible bids)
 *       401:
 *         description: Authentication required
 *       500:
 *         description: Selection failed
 */
router.post('/trigger', authenticateInternal, WinnerController.triggerManualSelection);

export default router;