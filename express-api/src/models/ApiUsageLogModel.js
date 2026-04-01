const {pool} = require('../config/database');

class ApiUsageLogModel {
    static async logRequest(logData){
        const {api_key_id, endpoint, http_method, source_ip} = logData;

        const truncatedEndpoint = endpoint.substring(0, 255);

        const [result] = await pool.execute(
            'INSERT INTO api_usage_logs (api_key_id, endpoint, http_method, source_ip, accessed_at) VALUES (?, ?, ?, ?, NOW())',
            [api_key_id, truncatedEndpoint, http_method, source_ip]
        );

        return result.insertId;
    }

    static async getTotalRequests(apiKeyId){
        const [rows] = await pool.execute(
            'SELECT COUNT(*) AS total_requests FROM api_usage_logs WHERE api_key_id = ?',
            [apiKeyId]
        );
        return rows[0].total;
    }

    static async getEndpointBreakdown(apiKeyId){
        const [rows] = await pool.execute(
            'SELECT endpoint, http_method, COUNT(*) AS count FROM api_usage_logs WHERE api_key_id = ? GROUPBY endpoint, http_method ORDER BY count DESC',
            [apiKeyId]
        );
        return rows;
    }


    static async getRecentRequests(apiKeyId, limit = 20){
        const [rows] = await pool.execute(
            'SELECT endpoint, http_method, source_ip, accessed_at FROM api_usage_logs WHERE api_key_id = ? ORDER BY accessed_at DESC LIMIT ?',
            [apiKeyId, limit]
        );
        return rows;
    }

    static async getUsageByDate(apiKeyId, startDate, endDate){
        const [totalRequest,endpointBreakdown,recentRequests] = await Promise.all([
            this.getTotalRequests(apiKeyId),
            this.getEndpointBreakdown(apiKeyId),
            this.getRecentRequests(apiKeyId)
        ]);

        return {
            total_requests: totalRequest,
            endpoint_breakdown: endpointBreakdown,
            recent_requests: recentRequests,
        };
    }

    static async getLogsByDateRange(apiKeyId, startDate, endDate){
        const [rows] = await pool.execute(
            'SELECT endpoint, http_method, source_ip, accessed_at FROM api_usage_logs WHERE api_key_id = ? AND accessed_at BETWEEN ? AND ? ORDER BY accessed_at DESC',
            [apiKeyId, startDate, endDate]
        );
        return rows;
    }
}
module.exports = ApiUsageLogModel;