import {pool} from "../config/database.js";
import {logger} from "../config/logger.js";

export const requestLogger = (req, res, next) => {
    const startTime = Date.now();
    res.on('finish', () => {
        const responseTime = Date.now() - startTime;

        // Build the log data object
        const logData = {
            method: req.method,
            url: req.originalUrl,
            status: res.statusCode,
            responseTime: `${responseTime}ms`,
            ip: req.ip,
            apiKeyId: req.apiKey ? req.apiKey.id : null,
            authType: req.authType || 'none',
            userAgent: req.headers['user-agent'] || 'unknown',
        };
        logger.http('API Request', logData);
        if (req.apiKey && req.apiKey.id) {
            logToDatabase(
                req.apiKey.id,
                req.originalUrl,
                req.method,
                req.ip
            );
        }
    });
    next();
};

const logToDatabase = (apiKeyId, endpoint, httpMethod, sourceIp) => {
    const truncatedEndpoint = endpoint.substring(0, 256);
    pool.execute(
        `INSERT INTO api_usage_logs (api_key_id, endpoint, http_method, source_ip, access_at)
         VALUES (?, ?, ?, ?, NOW())`,
        [apiKeyId, truncatedEndpoint, httpMethod, sourceIp]
    ).catch(error => {
        logger.error('Failed to log API usage to database:', {
            message: error.message,
            apiKeyId,
            endpoint: truncatedEndpoint,
        });
    });
};
