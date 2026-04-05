import express from 'express';
import { authenticateInternal } from '../middleware/authMiddleware.js';
import { validateSponsorshipResponse, validateIdParam } from '../middleware/inputValidator.js';
import SponsorshipController from '../controllers/SponsorshipController.js';

const router = express.Router();
/**
 * @swagger
 * /api/v1/sponsorships/sponsors:
 *   post:
 *     summary: Create a new sponsor organisation
 *     description: >
 *       Register a new sponsor (course provider, licensing body, or certification body).
 *       Sponsor name must be unique.
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [sponsor_name, sponsor_type, website_url]
 *             properties:
 *               sponsor_name:
 *                 type: string
 *                 example: "AWS Certification"
 *               sponsor_type:
 *                 type: string
 *                 enum: [course_provider, licensing_body, certification_body]
 *                 example: "certification_body"
 *               website_url:
 *                 type: string
 *                 example: "https://aws.amazon.com/certification"
 *     responses:
 *       201:
 *         description: Sponsor created
 *       400:
 *         description: Invalid sponsor type
 *       409:
 *         description: Duplicate sponsor name
 *       401:
 *         description: Authentication required
 */
router.post('/sponsors', authenticateInternal, SponsorshipController.createSponsor);

/**
 * @swagger
 * /api/v1/sponsorships/sponsors:
 *   get:
 *     summary: List all sponsors
 *     description: Returns all registered sponsors. Optional type filter.
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: type
 *         schema:
 *           type: string
 *           enum: [course_provider, licensing_body, certification_body]
 *         description: Filter by sponsor type
 *     responses:
 *       200:
 *         description: List of sponsors
 *       401:
 *         description: Authentication required
 */
router.get('/sponsors', authenticateInternal, SponsorshipController.listSponsors);

// ============================================================================
// OFFER ROUTES
// ============================================================================

/**
 * @swagger
 * /api/v1/sponsorships/offers:
 *   post:
 *     summary: Create a sponsorship offer
 *     description: >
 *       A sponsor offers money to an alumni to promote a specific credential.
 *       Uses a polymorphic relationship — sponsorable_type indicates which
 *       credential table (certificate, licence, professional_course) the
 *       sponsorable_id references.
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [sponsor_id, user_id, sponsorable_id, sponsorable_type, offer_amount]
 *             properties:
 *               sponsor_id:
 *                 type: integer
 *                 example: 1
 *               user_id:
 *                 type: integer
 *                 example: 5
 *               sponsorable_id:
 *                 type: integer
 *                 example: 3
 *                 description: ID of the credential in its respective table
 *               sponsorable_type:
 *                 type: string
 *                 enum: [certificate, licence, professional_course]
 *                 example: "certificate"
 *               offer_amount:
 *                 type: number
 *                 example: 200.00
 *     responses:
 *       201:
 *         description: Offer created
 *       404:
 *         description: Sponsor or credential not found
 *       401:
 *         description: Authentication required
 */
router.post('/offers', authenticateInternal, SponsorshipController.createOffer);

/**
 * @swagger
 * /api/v1/sponsorships/offers:
 *   get:
 *     summary: View sponsorship offers for a user
 *     description: >
 *       Returns all sponsorship offers with sponsor details and credential names.
 *       Optional status filter (pending, accepted, declined, paid).
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *       - in: query
 *         name: status
 *         schema:
 *           type: string
 *           enum: [pending, accepted, declined, paid]
 *         description: Filter by offer status
 *     responses:
 *       200:
 *         description: List of sponsorship offers
 *       401:
 *         description: Authentication required
 */
router.get('/offers', authenticateInternal, SponsorshipController.getOffers);

/**
 * @swagger
 * /api/v1/sponsorships/offers/{id}:
 *   put:
 *     summary: Accept or decline a sponsorship offer
 *     description: >
 *       The alumni responds to a pending sponsorship offer.
 *       Accepted offers add to the alumni's available bidding funds.
 *       Can only respond to offers in 'pending' status.
 *     tags: [Sponsorship]
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
 *             required: [action]
 *             properties:
 *               action:
 *                 type: string
 *                 enum: [accept, decline]
 *                 example: "accept"
 *               user_id:
 *                 type: integer
 *                 example: 5
 *     responses:
 *       200:
 *         description: Offer accepted/declined successfully
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 id: 1
 *                 status: "accepted"
 *                 offer_amount: 200.00
 *                 sponsorable_type: "certificate"
 *       400:
 *         description: Offer already responded to
 *       404:
 *         description: Offer not found
 *       401:
 *         description: Authentication required
 */
router.put(
    '/offers/:id',
    authenticateInternal,
    validateSponsorshipResponse,
    SponsorshipController.respondToOffer
);


/**
 * @swagger
 * /api/v1/sponsorships/balance:
 *   get:
 *     summary: View available sponsorship balance
 *     description: >
 *       Returns the total available bidding funds calculated as the
 *       sum of all accepted, unpaid sponsorship offer amounts.
 *       Also returns counts of accepted and paid offers.
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Balance details
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 available_balance: 500.00
 *                 total_accepted: 3
 *                 total_paid: 1
 *                 total_paid_amount: 200.00
 *       401:
 *         description: Authentication required
 */
router.get('/balance', authenticateInternal, SponsorshipController.getBalance);

/**
 * @swagger
 * /api/v1/sponsorships/summary:
 *   get:
 *     summary: View offer count summary by status
 *     description: Returns a breakdown of offer counts grouped by status.
 *     tags: [Sponsorship]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Offer summary
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 summary:
 *                   pending: 2
 *                   accepted: 3
 *                   declined: 1
 *                   paid: 1
 *       401:
 *         description: Authentication required
 */
router.get('/summary', authenticateInternal, SponsorshipController.getOfferSummary);

export default router;