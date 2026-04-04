import express from "express";
import BiddingController from "../services/BiddingService.js";


const router = express.Router();

/**
 * @openapi
 * /api/bids:
 *   post:
 *     summary: Place a new bid (Blind bidding)
 *     tags: [Bidding]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               bid_amount:
 *                 type: number
 *                 example: 500
 *               bid_date:
 *                 type: string
 *                 example: "2026-04-10"
 *               sponsorship_total:
 *                 type: number
 *                 example: 1000
 *     responses:
 *       200:
 *         description: Bid placed successfully (winning/losing status returned)
 */
router.post("/", BiddingController.placeBid);

/**
 * @openapi
 * /api/bids/{id}:
 *   put:
 *     summary: Update bid amount
 *     tags: [Bidding]
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         description: Bid ID
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               amount:
 *                 type: number
 *                 example: 600
 *     responses:
 *       200:
 *         description: Bid updated successfully
 */
router.put("/:id", BiddingController.updateBid);

/**
 * @openapi
 * /api/bids/{id}:
 *   delete:
 *     summary: Cancel a bid
 *     tags: [Bidding]
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         description: Bid ID
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Bid cancelled successfully
 */
router.delete("/:id", BiddingController.cancelBid);

/**
 * @openapi
 * /api/bids/history:
 *   get:
 *     summary: Get user bid history (paginated)
 *     tags: [Bidding]
 *     parameters:
 *       - in: query
 *         name: page
 *         required: false
 *         schema:
 *           type: integer
 *           example: 1
 *       - in: query
 *         name: limit
 *         required: false
 *         schema:
 *           type: integer
 *           example: 10
 *     responses:
 *       200:
 *         description: Bid history retrieved successfully
 */
router.get("/history", BiddingController.getUserHistory);

/**
 * @openapi
 * /api/bids/winner:
 *   post:
 *     summary: Select winning bid for a specific date
 *     tags: [Bidding]
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               date:
 *                 type: string
 *                 example: "2026-04-10"
 *     responses:
 *       200:
 *         description: Winner selected successfully
 */
router.post("/winner", BiddingController.selectWinner);

/**
 * @openapi
 * /api/bids/{id}/status:
 *   get:
 *     summary: Check if a bid is winning or losing (Blind bidding)
 *     tags: [Bidding]
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         description: Bid ID
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Bid status retrieved successfully
 */
//router.get("/:id/status", BiddingController.);

export default router;