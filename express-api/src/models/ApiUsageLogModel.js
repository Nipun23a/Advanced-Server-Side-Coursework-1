import { pool } from '../config/database.js';

class ApiUsageLogModel {
    static async logUsage(apiKeyId, endpoint, httpMethod, sourceIp) {
        const truncatedEndpoint = endpoint.substring(0, 256);
        const [result] = await pool.execute(
            `INSERT INTO api_usage_logs (api_key_id, endpoint, http_method, source_ip, access_at)
             VALUES (?, ?, ?, ?, NOW())`,
            [apiKeyId, truncatedEndpoint, httpMethod, sourceIp]
        );
        return result;
    }

    static async getTotalRequests(apiKeyId) {
        const [rows] = await pool.execute(
            'SELECT COUNT(*) AS total FROM api_usage_logs WHERE api_key_id = ?',
            [apiKeyId]
        );
        return rows[0].total;
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
            `SELECT endpoint, http_method, source_ip, access_at
             FROM api_usage_logs
             WHERE api_key_id = ?
             ORDER BY access_at DESC
                 LIMIT ?`,
            [apiKeyId, limit]
        );
        return rows;
    }

    static async getFullStats(apiKeyId) {
        const [totalRequests, endpointBreakdown, recentRequests] = await Promise.all([
            this.getTotalRequests(apiKeyId),
            this.getEndpointBreakdown(apiKeyId),
            this.getRecentRequests(apiKeyId),
        ]);

        return {
            total_requests: totalRequests,
            endpoint_breakdown: endpointBreakdown,
            recent_requests: recentRequests,
        };
    }

    static async getLogsByDateRange(apiKeyId, startDate, endDate) {
        const [rows] = await pool.execute(
            `SELECT endpoint, http_method, source_ip, access_at
             FROM api_usage_logs
             WHERE api_key_id = ? AND DATE(access_at) BETWEEN ? AND ?
             ORDER BY accessed_at DESC`,
            [apiKeyId, startDate, endDate]
        );
        return rows;
    }
}

export default ApiUsageLogModel;