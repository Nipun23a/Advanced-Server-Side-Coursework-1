const TokenGeneration = require('../utils/tokenGeneration');
const crypto = require('crypto');
const pool = require("../config/database");
const logger = require('../config/logger');
class ApiKeyService {

    static async generateApiKey(userId) {
        const rawKey = TokenGeneration.generateAPIKey();
        const hashedKey = TokenGeneration.hashToken(rawKey);

        const [result] = await pool.execute(
            "INSERT INTO api_keys (user_id, hashed_key,is_active,created_at) VALUES (?, ?,true,NOW())",
            [userId, hashedKey]
        );

        logger.info(`API key generated: key_id=${result.insertId}, user_id=${userId}`);
        return  {key_id: result.insertId, key: rawKey,warning : 'Save this key now. You will not be able to see it again!'};

    }

    static async validateApiKey(rawKey) {
        if (!TokenGeneration.isValidAPIKeyFormat(rawKey)) {
            logger.warn('API key format validation failed');
            return null;
        }
        const hashedKey = TokenGeneration.hashToken(rawKey);

        const [rows] = await pool.execute(
            `SELECT ak.id, ak.user_id, ak.is_active, ak.revoked_at, u.email, u.role
             FROM api_keys ak
             JOIN users u ON ak.user_id = u.id
             WHERE ak.hashed_key = ?`,
            [hashedKey]
        );
        if (rows.length === 0) {
            return null;
        }
        const apiKey = rows[0];
        if (!apiKey.is_active || apiKey.revoked_at !== null) {
            logger.warn(`Revoked API key attempted: key_id=${apiKey.id}`);
            return null;
        }
        return apiKey;
    }

    static async listKeys(userId) {
        const [rows] = await pool.execute(
            'SELECT id, is_active, created_at, revoked_at FROM api_keys WHERE user_id = ? ORDER BY created_at DESC',
            [userId]
        );
        return { keys: rows };
    }

    static async getKeyStats(keyId) {
        const [keyRows] = await pool.execute(
            'SELECT id, is_active, created_at, revoked_at FROM api_keys WHERE id = ?',
            [keyId]
        );
        if (keyRows.length === 0) return null;
        const [totalRequests, endpointStats, recentRequests] = await Promise.all([
            pool.execute(
                'SELECT COUNT(*) AS total FROM api_usage_logs WHERE api_key_id = ?',
                [keyId]
            ),
            pool.execute(
                `SELECT endpoint, http_method, COUNT(*) AS count 
                 FROM api_usage_logs WHERE api_key_id = ? 
                 GROUP BY endpoint, http_method ORDER BY count DESC`,
                [keyId]
            ),
            pool.execute(
                `SELECT endpoint, http_method, source_ip, accessed_at 
                 FROM api_usage_logs WHERE api_key_id = ? 
                 ORDER BY accessed_at DESC LIMIT 20`,
                [keyId]
            ),
        ]);
        return {
            key: {
                id: keyRows[0].id,
                is_active: keyRows[0].is_active,
                created_at: keyRows[0].created_at,
                revoked_at: keyRows[0].revoked_at,
            },
            statistics: {
                total_requests: totalRequests[0][0].total,
                endpoint_breakdown: endpointStats[0],
                recent_requests: recentRequests[0],
            },
        };
    }

    static async revokeKey(keyId,userId){
        const [revokeKeys] = await pool.execute(`SELECT * FROM api_keys WHERE id = ? AND user_id = ?`, [keyId,userId]);
        if (revokeKeys.length === 0){
            const error = new Error('API Key not found');
            error.code = 'KEY NOT FOUND';
            error.status = 404;
            throw error;
        }
        await pool.execute(`UPDATE api_keys SET is_active = false, revoked_at = NOW() WHERE id = ?`, [keyId]);
        logger.info(`API key revoked: key_id=${keyId}, user_id=${userId}`);
        return {id:keyId,is_active:false,revoked_at:new Date().toISOString()};
     }

}

module.exports = ApiKeyService;