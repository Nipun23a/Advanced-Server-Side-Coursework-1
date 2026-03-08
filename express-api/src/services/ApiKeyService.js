const crypto = require('crypto');
const pool = require("../config/database");
const logger = require('../config/logger');
class ApiKeyService {

    static async generateApiKey(userId) {
        const prefix = process.env.API_KEY_PREFIX || 'alum_';
        const keyLength = parseInt(process.env.API_KEY_LENGTH) || 64;

        const rawKey = prefix + crypto.randomBytes(keyLength).toString();
        const hashedKey = crypto.createHash('sha256').update(rawKey).digest('hex');

        const [result] = await pool.execute(
            "INSERT INTO api_keys (user_id, hashed_key,is_active,created_at) VALUES (?, ?,true,NOW())",
            [userId, hashedKey]
        );

        logger.info(`API key generated: key_id=${result.insertId}, user_id=${userId}`);
        return  {key_id: result.insertId, key: rawKey,warning : 'Save this key now. You will not be able to see it again!'};

    }

    static async lisKeys(userId){
        const [rows] = await pool.execute(
            'SELECT * FROM api_keys WHERE user_id = ?',
            [userId]
        );
        return {keys:rows};
    }

    static async getKeyStats(keyId){
        const [keysRows] = await pool.execute(
            'SELECT  * FROM api_keys WHERE id = ?',[keyId]
        );
        if (keysRows.length === 0)
            return null;

        const [totalRequests] = await pool.execute(`SELECT COUNT(*) AS total FROM api_usage_logs WHERE api_key_id = ?`, [keyId]);
        const [endpointStats] = await pool.execute(`SELECT endpoint, http_method, COUNT(*) AS count FROM api_usage_logs WHERE api_key_id = ? GROUP BY endpoint, http_method ORDER BY count DESC`, [keyId]);
        const [recentRequests] = await pool.execute(`SELECT endpoint, http_method, source_ip, accessed_at FROM api_usage_logs WHERE api_key_id = ? ORDER BY accessed_at DESC LIMIT 20`, [keyId]);

        return {
            key: {id:keysRows[0].id,is_active:keysRows[0].is_active,created_at:keysRows[0].created_at,revoked_at:keysRows[0].revoked_at,},
            statics: {total_requests:totalRequests[0].total,endpoint_stats:endpointStats,recent_requests:recentRequests}
        }
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