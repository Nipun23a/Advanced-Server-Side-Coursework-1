import winston from "winston";
import path from "path";
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export const logger = winston.createLogger({
    level: process.env.LOG_LEVEL || 'debug',
    format: winston.format.combine(
        winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
        winston.format.errors({ stack: true }),
        winston.format.json()
    ),

    defaultMeta: { service: 'alumni-influencers-api' },

    transports: [
        new winston.transports.File({
            filename: process.env.LOG_FILE || path.join(__dirname, '../logs/error.log'),
            maxsize: 5242880,
            maxFiles: 5,
        }),

        new winston.transports.File({
            filename: path.join(__dirname, '../logs/combined.log')
        })
    ]
});