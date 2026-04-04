import rateLimit from 'express-rate-limit';
import {logger} from "../config/logger.js";
import {sendError} from "../utils/responseHelper.js";

// general rate limiter
export const generalLimiter = rateLimit({
    windowMs:parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000,
    max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100,
    standardHeaders:true,
    legacyHeaders:false,
    handler:(req,res) => {
        logger.warn('Rate limit exceeded',{
            ip:req.ip,
            url:req.originalUrl,
            method:req.method,
            limiter: 'general',
        });
        sendError(
            res,
            'RATE_LIMIT_EXCEEDED',
            'Too many requests. Please try again later',
            429
        );
    },
    keyGenerator: (req) => {
        return req.ip;
    }
});

// bidding rate limiter
export const biddingLimiter = rateLimit({
    windowMs:parseInt(process.env.RATE_LIMIT_BID_WINDOW_MS),
    max:parseInt(process.env.RATE_LIMIT_BID_MAX_REQUESTS),
    standardHeaders:true,
    legacyHeaders:false,
    handler: (req,res) => {
        logger.warn('Bidding rate limit exceeded', {
            ip: req.ip,
            url: req.originalUrl,
            method: req.method,
            limiter: 'bidding',
        });
        sendError(
            res,
            'RATE_LIMIT_EXCEEDED',
            'Too many bidding requests. Please wait before trying again.',
            429
        );
    },
    keyGenerator:(req) => {
        return req.ip;
    }
});
// Authentication Rate Limiter
export const authLimiter = rateLimit({
    windowMs:parseInt(process.env.RATE_LIMIT_AUTH_WINDOW_MS) || 15 * 60 * 1000,
    max:parseInt(process.env.RATE_LIMIT_AUTH_MAX_REQUESTS) || 5,
    standardHeaders:true,
    legacyHeaders:false,

    handler:(req,res) => {
        logger.warn('Auth rate limit exceeded',{
            ip: req.ip,
            url: req.originalUrl,
            method: req.method,
            limiter: 'auth',
        });
        sendError(
            res,
            'RATE_LIMIT_EXCEEDED',
            'Too many authentication attempts. Please try again in 15 minutes',
            429
        );
    },
    keyGenerator:(req) => {
        return req.ip;
    },
});

