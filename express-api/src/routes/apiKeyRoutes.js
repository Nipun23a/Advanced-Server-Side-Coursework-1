import express from 'express';
import { authenticateInternal } from '../middleware/authMiddleware.js';
import { validateGenerateKey, validateRevokeKey, validateIdParam } from '../middleware/inputValidator.js';
import ApiKeyController from '../controllers/ApiKeyController.js';

const router = express.Router();

/**
 * @swagger
 * /api/v1/api-keys:
 *   post:
 *     summary: Generate a new API key
 *     description: >
 *       Generates a cryptographically secure API key using crypto.randomBytes(32).
 *       The raw key is returned ONLY in this response — it is hashed with SHA-256
 *       before storage and cannot be retrieved again. The user must copy and save
 *       the key immediately.
 *     tags: [API Keys]
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
 *               client_type:
 *                 type: string
 *                 enum: [analytics_dashboard, ar_app, third_party]
 *                 description: >
 *                   Optional. When provided, permissions and scope are automatically
 *                   assigned based on the client type. Omit for a plain key with no scope.
 *                 example: ar_app
 *     responses:
 *       201:
 *         description: API key generated successfully
 *         content:
 *           application/json:
 *             examples:
 *               plain:
 *                 summary: Key without scope
 *                 value:
 *                   success: true
 *                   data:
 *                     key_id: 1
 *                     key: "alum_a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6a7b8c9d0e1f2a3b4c5d6a7b8c9d0e1f2"
 *                     warning: "Save this key now. You will not be able to see it again!"
 *                   message: "API key generated successfully. Save this key - it cannot be retrieved."
 *               scoped:
 *                 summary: Key with scope
 *                 value:
 *                   success: true
 *                   data:
 *                     key_id: 2
 *                     key: "alum_a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6a7b8c9d0e1f2a3b4c5d6a7b8c9d0e1f2"
 *                     client_type: "ar_app"
 *                     permissions: ["read:alumni_of_day"]
 *                     warning: "Save this key now. You will not be able to see it again!"
 *                   message: "API key generated successfully. Save this key - it cannot be retrieved."
 *       400:
 *         description: Missing user_id or invalid client_type
 *       401:
 *         description: Internal authentication required
 */
router.post(
    '/',
    authenticateInternal,
    validateGenerateKey,
    ApiKeyController.generateKey
);

/**
 * @swagger
 * /api/v1/api-keys:
 *   get:
 *     summary: List all API keys for a user
 *     description: >
 *       Returns all API keys (active and revoked) with metadata only.
 *       Never returns the raw key or the hash.
 *     tags: [API Keys]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: query
 *         name: user_id
 *         required: true
 *         schema:
 *           type: integer
 *         description: The user whose keys to list
 *     responses:
 *       200:
 *         description: List of API keys
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 keys:
 *                   - id: 1
 *                     is_active: true
 *                     created_at: "2026-04-01T10:00:00.000Z"
 *                     revoked_at: null
 *                   - id: 2
 *                     is_active: false
 *                     created_at: "2026-03-15T08:30:00.000Z"
 *                     revoked_at: "2026-03-20T14:00:00.000Z"
 *       400:
 *         description: Missing user_id
 *       401:
 *         description: Internal authentication required
 */
router.get(
    '/',
    authenticateInternal,
    ApiKeyController.listKeys
);

/**
 * @swagger
 * /api/v1/api-keys/{id}/stats:
 *   get:
 *     summary: View usage statistics for an API key
 *     description: >
 *       Returns comprehensive usage statistics including total request count,
 *       endpoint access breakdown (which endpoints were hit and how often),
 *       and the 20 most recent requests with timestamps and source IP addresses.
 *       This endpoint directly addresses the coursework requirement for
 *       viewing API key usage statistics.
 *     tags: [API Keys]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: The API key ID to get statistics for
 *     responses:
 *       200:
 *         description: Usage statistics
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 key:
 *                   id: 1
 *                   is_active: true
 *                   created_at: "2026-04-01T10:00:00.000Z"
 *                   revoked_at: null
 *                 statistics:
 *                   total_requests: 156
 *                   endpoint_breakdown:
 *                     - endpoint: "/api/v1/public/featured-alumni/today"
 *                       http_method: "GET"
 *                       count: 120
 *                     - endpoint: "/api/v1/public/featured-alumni/history"
 *                       http_method: "GET"
 *                       count: 36
 *                   recent_requests:
 *                     - endpoint: "/api/v1/public/featured-alumni/today"
 *                       http_method: "GET"
 *                       source_ip: "192.168.1.100"
 *                       accessed_at: "2026-04-04T14:30:00.000Z"
 *       404:
 *         description: API key not found
 *       401:
 *         description: Internal authentication required
 */
router.get(
    '/:id/stats',
    authenticateInternal,
    validateIdParam,
    ApiKeyController.getKeyStats
);

/**
 * @swagger
 * /api/v1/api-keys/{id}:
 *   delete:
 *     summary: Revoke an API key
 *     description: >
 *       Immediately revokes an API key. The key will no longer authenticate
 *       any requests from the moment of revocation. This action cannot be
 *       undone. Ownership is verified — users can only revoke their own keys.
 *     tags: [API Keys]
 *     security:
 *       - InternalAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *         description: The API key ID to revoke
 *     requestBody:
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               user_id:
 *                 type: integer
 *                 description: Owner's user ID for ownership verification
 *     responses:
 *       200:
 *         description: API key revoked successfully
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 id: 1
 *                 is_active: false
 *                 revoked_at: "2026-04-04T15:00:00.000Z"
 *               message: "API key revoked successfully."
 *       400:
 *         description: Key already revoked or missing user_id
 *       404:
 *         description: API key not found or doesn't belong to user
 *       401:
 *         description: Internal authentication required
 */
router.delete(
    '/:id',
    authenticateInternal,
    validateRevokeKey,
    ApiKeyController.revokeKey
);

export default router;