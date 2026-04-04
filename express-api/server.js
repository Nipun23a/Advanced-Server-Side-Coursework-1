import express from "express";
import dotenv from "dotenv";
import cors from 'cors';

import {testConnection} from "./src/config/database.js";

import {helmetMiddleware} from "./src/middleware/helmet.js";
import {apiLimiter} from "./src/middleware/rateLimiter.js";
import {errorHandler} from "./src/middleware/errorHandler.js";
import {requestLogger} from "./src/middleware/requestLogger.js";

import SponsorshipRoutes from "./src/routes/sponsorshipRoutes.js";
import apiKeyRoutes from "./src/routes/apiKeyRoutes.js";

import swaggerUi from 'swagger-ui-express';
import swaggerSpec from "./src/config/swagger.js";

import cron from 'node-cron';



dotenv.config();

const app = express();

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({extended: true}));

app.use(helmetMiddleware);
app.use(apiLimiter);
app.use(requestLogger);

app.get("/", (req, res) => {
    res.json({
        message: "Welcome to the Alumni Influencers API"
    });
});

app.get("/test-api", (req, res) => {
    res.json({ message: "Test route working" });
});

app.use("/api/sponsorships", SponsorshipRoutes);
console.log("API Key routes loaded");
app.use("/api/v1/api-keys", apiKeyRoutes);

app.use("/api-docs", swaggerUi.serve, swaggerUi.setup(swaggerSpec));

app.use(errorHandler);

const PORT = process.env.PORT || 3000;
const startServer = async () => {
    try {
        await testConnection();
        app.listen(PORT,() => {
            console.log(`Server running on port ${PORT}`);
            console.log(`Swagger docs available at http://localhost:${PORT}/api-docs`);
        });
    } catch (error) {
        console.error("Failed to start server:", error);
        process.exit(1);
    }
}

startServer();

