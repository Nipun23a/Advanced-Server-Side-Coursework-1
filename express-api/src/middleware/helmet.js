import helmet from "helmet";

export const helmetConfig = helmet({
    contentSecurityPolicy:{
        directives:{
            defaultSrc:["'self'"],
            scriptSrc:["'self'"],
            styleSrc:["'self'","'unsafe-inline'"],
            imgSrc: ["'self'", 'data:'],
            connectSrc: ["'self'"],
            fontSrc: ["'self'"],
            objectSrc: ["'none'"],
            mediaSrc: ["'none'"],
            frameSrc: ["'none'"],
        },
    },

    crossOriginEmbedderPolicy:false,
    frameguard:{action:'deny'},
    hsts: {
        maxAge: 31536000,
        includeSubDomains: true,
        preload: true,
    },
    referrerPolicy:{policy:'strict-origin-when-cross-origin'},
    dnsPrefetchControl:{allow:false},
    noSniff:true,
    xssFilter:true
})

