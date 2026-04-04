import { pool } from '../config/database.js';

class ApiUsageLogModel {

    static async logRequest({ api_key_id, endpoint, http_method, source_ip }) {
        const truncatedEndpoint = endpoint.substring(0, 255);

        const [result] = await pool.execute(
            `INSERT INTO api_usage_logs 
             (api_key_id, endpoint, http_method, source_ip, accessed_at)
             VALUES (?, ?, ?, ?, NOW())`,
            [api_key_id, truncatedEndpoint, http_method, source_ip]
        );

        return result.insertId;
    }

    static async getTotalRequests(apiKeyId) {
        const [rows] = await pool.execute(
            `SELECT COUNT(*) AS total_requests 
             FROM api_usage_logs 
             WHERE api_key_id = ?`,
            [apiKeyId]
        );
        return rows[0].total_requests;
    }

    static async getEndpointBreakdown(apiKeyId) {
        const [rows] = await pool.execute(
            `SELECT endpoint, http_method, COUNT(*) AS count 
             FROM api_usage_logs 
             WHERE api_key_id = ?
             GROUP BY endpoint, http_method
             ORDER BY count DESC`,
            [apiKeyId]
        );
        return rows;
    }

    static async getRecentRequests(apiKeyId, limit = 20) {
        const [rows] = await pool.execute(
            `SELECT endpoint, http_method, source_ip, accessed_at 
             FROM api_usage_logs 
             WHERE api_key_id = ?
             ORDER BY accessed_at DESC 
             LIMIT ?`,
            [apiKeyId, limit]
        );
        return rows;
    }

    static async getUsageByDate(apiKeyId) {
        const [total, breakdown, recent] = await Promise.all([
            this.getTotalRequests(apiKeyId),
            this.getEndpointBreakdown(apiKeyId),
            this.getRecentRequests(apiKeyId)
        ]);

        return {
            total_requests: total,
            endpoint_breakdown: breakdown,
            recent_requests: recent
        };
    }
}

export default ApiUsageLogModel;