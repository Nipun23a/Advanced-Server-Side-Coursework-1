import express from 'express';
import {authenticateAny, authenticateBearer} from "../middleware/authMiddleware.js";
import {validatePagination} from "../middleware/inputValidator.js";
import WinnerController from "../controllers/winnerController.js";

const router = express.Router();

/**
 * @swagger
 * /api/v1/public/featured-alumni/today:
 *   get:
 *     summary: Get today's featured Alumni of the Day
 *     description: >
 *       Returns the complete profile of today's featured alumni including
 *       personal information, biography, LinkedIn URL, profile image,
 *       degrees, certificates, licences, professional courses, and
 *       employment history. This is the primary endpoint consumed by
 *       the augmented reality client. Does NOT include bid amounts.
 *     tags: [Public API]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Today's featured alumni profile
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/FeaturedAlumni'
 *       401:
 *         description: Missing or invalid API key
 *       404:
 *         description: No alumni featured today
 */
router.get('/featured-alumni/today', authenticateBearer, WinnerController.getTodayFeatures);

/**
 * @swagger
 * /api/v1/public/featured-alumni/history:
 *   get:
 *     summary: Get featured alumni history
 *     description: Returns a paginated list of past featured alumni with profile details. Does NOT include bid amounts.
 *     tags: [Public API]
 *     security:
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
 *         description: Paginated list of featured alumni
 *       401:
 *         description: Missing or invalid API key
 */
router.get('/featured-alumni/history', authenticateBearer, validatePagination, WinnerController.getFeaturedHistory);

export default router;