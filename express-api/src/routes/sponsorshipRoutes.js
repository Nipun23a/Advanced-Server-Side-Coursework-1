import express from 'express';

import { authMiddleware } from "../middleware/authMiddleware.js";
import { apiLimiter } from "../middleware/rateLimiter.js";
import SponsorshipController from "../controllers/sponsorshipController.js";

const router = express.Router();

router.use(authMiddleware);
router.use(apiLimiter);


/**
 * @openapi
 * /api/sponsorships:
 *   get:
 *     summary: Get all sponsorship offers for logged-in user
 *     tags:
 *       - Sponsorship
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: List of sponsorship offers
 */
router.get("/", SponsorshipController.getMyOffers);


/**
 * @openapi
 * /api/sponsorships/balance:
 *   get:
 *     summary: Get sponsorship balance
 *     tags:
 *       - Sponsorship
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Sponsorship balance details
 */
router.get("/balance", SponsorshipController.getBalance);


/**
 * @openapi
 * /api/sponsorships/{id}:
 *   get:
 *     summary: Get a specific sponsorship offer
 *     tags:
 *       - Sponsorship
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Sponsorship offer details
 */
router.get("/:id", SponsorshipController.getOffer);


/**
 * @openapi
 * /api/sponsorships/{id}/accept:
 *   put:
 *     summary: Accept a sponsorship offer
 *     tags:
 *       - Sponsorship
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Offer accepted
 */
router.put("/:id/accept", SponsorshipController.acceptOffer);


/**
 * @openapi
 * /api/sponsorships/{id}/decline:
 *   put:
 *     summary: Decline a sponsorship offer
 *     tags:
 *       - Sponsorship
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Offer declined
 */
router.put("/:id/decline", SponsorshipController.declineOffer);


export default router;