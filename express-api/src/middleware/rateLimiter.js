const {rateLimit} = require('express-rate-limit');

const rateLimiter = rateLimit({
    windowMs : 15 * 60 * 1000,
    max : 100,
    message : {
        error: 'Too many requests from this IP, please try again later.'
    },
});

const authLimiter = rateLimit({
    windowMs : 15 * 60 * 1000,
    max : 10,
    message : {
        error: 'Too many login attempts from this IP, please try again later.'
    },
})

module.exports = {
    rateLimiter,
    authLimiter,
}