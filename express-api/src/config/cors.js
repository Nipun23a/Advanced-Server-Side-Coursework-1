export const corsOptions = {
    origin: function (origin, callback) {
        if (!origin) {
            return callback(null, true);
        }

        const allowedOrigins = process.env.CORS_ORIGIN
            ? process.env.CORS_ORIGIN.split(',').map(o => o.trim())
            : ['http://localhost:8080', 'http://localhost:3000'];

        if (allowedOrigins.includes(origin)) {
            callback(null, true);
        }else{
            callback(new Error(`Origin ${origin} not allowed by CORS policy`));
        }
    },
    method : (process.env.CORS_METHODS || 'GET,HEAD,PUT,PATCH,POST,DELETE').split(',').map(method => method.trim())
    ,
    allowedHeaders: [
        'Content-Type',
        'Authorization',
        'X-Internal-Secret',
        'X-Request-ID',
    ],

    exposedHeaders: [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
        'Retry-After',
    ],

    credentials: process.env.CORS_CREDENTIALS === 'true',

    maxAge: 86400,

}

