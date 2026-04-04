import dotenv from "dotenv";
dotenv.config();

import {helmetConfig} from "./src/middleware/helmet.js";
import {generalLimiter,biddingLimiter,authLimiter} from "./src/middleware/rateLimiter.js";

import express from "express";
import {corsOptions} from "./src/config/cors.js";
import cors from "cors";
import {requestLogger} from "./src/middleware/requestLogger.js";
import swaggerUi from "swagger-ui-express";
import swaggerSpec from "./src/config/swagger.js";
import sponsorshipRoutes from "./src/routes/sponsorshipRoutes.js";
import apiKeyRoutes from "./src/routes/apiKeyRoutes.js";
import winnerRoutes from "./src/routes/winnerRoutes.js";
import biddingRoutes from "./src/routes/biddingRoutes.js";
import {errorHandler, notFoundHandler} from "./src/middleware/errorHandler.js";
import {logger} from "./src/config/logger.js";
import startCronJobs from "./src/cron/WinerSelection.js";
import {testConnection} from "./src/config/database.js";

const app = express();
const PORT = process.env.PORT || 3000;

app.set("trust proxy", 1);
app.use(helmetConfig);
app.use(cors(corsOptions));

app.use(express.json({ limit: '10kb' }));
app.use(express.urlencoded({ extended: true, limit: '10kb' }));

app.use('/api/', generalLimiter);
app.use('/api/',requestLogger);

app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec, {
    customCss: '.swagger-ui .topbar { display: none }',
    customSiteTitle: 'Alumni Influencers API Documentation',
    swaggerOptions: {
        persistAuthorization: true,
    },
}));

app.get('/api-docs.json', (req, res) => {
    res.setHeader('Content-Type', 'application/json');
    res.send(swaggerSpec);
});

app.get('/api/v1/health', (req, res) => {
    res.status(200).json({
        success: true,
        data: {
            status: 'healthy',
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            environment: process.env.NODE_ENV || 'development',
        },
        message: 'API is running.',
    });
});

//app.use('/api/v1/public', publicRoutes);
app.use('/api/v1/bids', biddingRoutes);
app.use('/api/v1/winners', winnerRoutes);
app.use('/api/v1/api-keys', apiKeyRoutes);
app.use('/api/v1/sponsorships',sponsorshipRoutes);

app.use(notFoundHandler);
app.use(errorHandler);

const startServer = async () => {
    try {
        // Step 1: Verify database connection
        logger.info('Testing database connection...');
        await testConnection();

        // Step 2: Start scheduled cron jobs
        logger.info('Starting cron jobs...');
        startCronJobs();

        // Step 3: Start HTTP server
        app.listen(PORT, () => {
            logger.info('===========================================');
            logger.info('  Alumni Influencers API Server Started');
            logger.info('===========================================');
            logger.info(`  Port:        ${PORT}`);
            logger.info(`  Environment: ${process.env.NODE_ENV || 'development'}`);
            logger.info(`  Swagger:     http://localhost:${PORT}/api-docs`);
            logger.info(`  Health:      http://localhost:${PORT}/api/v1/health`);
            logger.info('===========================================');
        });

        console.log()

    } catch (error) {
        logger.error('Failed to start server:', {
            message: error.message,
            stack: error.stack,
        });
        process.exit(1);
    }
};

const gracefulShutdown = async (signal) => {
    logger.info(`${signal} received. Shutting down gracefully...`);
    const { pool } = await import('./src/config/database.js');
    await pool.end();
    logger.info('Database pool closed. Exiting.');
    process.exit(0);
};

process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => gracefulShutdown('SIGINT'));

startServer();

export default app;




