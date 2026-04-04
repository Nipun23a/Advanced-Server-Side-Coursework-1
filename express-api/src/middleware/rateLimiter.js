import rateLimit, { ipKeyGenerator } from 'express-rate-limit';
import { logger } from "../config/logger.js";
import { sendError } from "../utils/responseHelper.js";

/**
 * Safe integer parser
 */
const toInt = (value, fallback) => {
    const parsed = parseInt(value, 10);
    return isNaN(parsed) ? fallback : parsed;
};

/**
 * Common handler (DRY)
 */
const createHandler = (type, message) => {
    return (req, res) => {
        logger.warn(`${type} rate limit exceeded`, {
            ip: req.ip,
            url: req.originalUrl,
            method: req.method,
            limiter: type,
        });

        sendError(res, 'RATE_LIMIT_EXCEEDED', message, 429);
    };
};

/**
 * General Rate Limiter
 */
export const generalLimiter = rateLimit({
    windowMs: toInt(process.env.RATE_LIMIT_WINDOW_MS, 15 * 60 * 1000), // 15 min
    max: toInt(process.env.RATE_LIMIT_MAX_REQUESTS, 100),
    standardHeaders: true,
    legacyHeaders: false,
    keyGenerator: (req) => ipKeyGenerator(req),
    handler: createHandler('general', 'Too many requests. Please try again later.'),
});

/**
 * Bidding Rate Limiter
 */
export const biddingLimiter = rateLimit({
    windowMs: toInt(process.env.RATE_LIMIT_BID_WINDOW_MS, 60 * 1000), // 1 min
    max: toInt(process.env.RATE_LIMIT_BID_MAX_REQUESTS, 10),
    standardHeaders: true,
    legacyHeaders: false,
    keyGenerator: (req) => ipKeyGenerator(req),
    handler: createHandler('bidding', 'Too many bidding requests. Please wait before trying again.'),
});

/**
 * Authentication Rate Limiter
 */
export const authLimiter = rateLimit({
    windowMs: toInt(process.env.RATE_LIMIT_AUTH_WINDOW_MS, 15 * 60 * 1000), // 15 min
    max: toInt(process.env.RATE_LIMIT_AUTH_MAX_REQUESTS, 5),
    standardHeaders: true,
    legacyHeaders: false,
    keyGenerator: (req) => ipKeyGenerator(req),
    handler: createHandler('auth', 'Too many authentication attempts. Please try again later.'),
});