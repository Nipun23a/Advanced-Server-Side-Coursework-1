import rateLimit from 'express-rate-limit';

export const rateLimiter = rateLimit({
    windowMs : 15 * 60 * 1000,
    max : 100,
    message : {
        error: 'Too many requests from this IP, please try again later.'
    },
});

export const authLimiter = rateLimit({
    windowMs : 15 * 60 * 1000,
    max : 10,
    message : {
        error: 'Too many login attempts from this IP, please try again later.'
    },
});
