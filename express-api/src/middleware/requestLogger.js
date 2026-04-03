const db = require("../config/database");

const requestLogger = async (req, res, next) => {
    const start = Date.now();

    res.on("finish", async () => {
        try {
        if (!req.apiKey) return;

        await db.execute(
            `INSERT INTO api_usage_logs 
            (api_key_id, endpoint, http_method, accessed_at, source_ip) 
            VALUES (?, ?, ?, NOW(), ?)`,
            [
            req.apiKey.id,
            req.originalUrl,
            req.method,
            req.ip,
            ]
        );
        } catch (err) {
            console.error("Failed to log API usage:", err);
        }
    });
    next();
}

module.exports = requestLogger;