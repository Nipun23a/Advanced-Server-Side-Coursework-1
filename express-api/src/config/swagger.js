import swaggerJsDoc from "swagger-jsdoc";

const swaggerOptions = {
    definition: {
        openapi: "3.0.0",
        info: {
            title: "Alumni Influencer API",
            version: "1.0.0",
            description: `
RESTful API for the Alumni Influencers Platform.

This API serves as the business logic layer for the Alumni Influencers platform,
providing endpoints for blind bidding, featured alumni retrieval, API key management,
and sponsorship operations.

Authentication:
- External clients: Bearer token in Authorization header
- Internal (CodeIgniter): X-Internal-Secret header
            `,
            contact: {
                name: "Alumni Influencers Platform",
                email: "support@eatminster.ac.uk"
            }
        },
        servers: [
            {
                url: `http://localhost:${process.env.PORT || 3000}`,
                description: "Development Server"
            }
        ],
        components: {
            securitySchemes: {
                BearerAuth: {
                    type: "http",
                    scheme: "bearer",
                    bearerFormat: "JWT"
                },
                InternalAuth: {
                    type: "apiKey",
                    in: "header",
                    name: "X-Internal-Secret",
                    description: "Internal service authentication"
                }
            },
            schemas: {
                Error: {
                    type: "object",
                    properties: {
                        success: { type: "boolean", example: false },
                        error: {
                            type: "object",
                            properties: {
                                code: { type: "string", example: "VALIDATION_ERROR" },
                                message: { type: "string", example: "Invalid input provided" },
                                details: { type: "array", items: { type: "object" } }
                            }
                        }
                    }
                },
                Success: {
                    type: "object",
                    properties: {
                        success: { type: "boolean", example: true },
                        data: { type: "object" },
                        message: { type: "string" }
                    }
                },
                Bid: {
                    type: "object",
                    properties: {
                        id: { type: "integer", example: 1 },
                        user_id: { type: "integer", example: 5 },
                        bid_amount: { type: "number", example: 250.0 },
                        bid_status: { type: "string", enum: ["active", "won", "lost"] },
                        bid_date: { type: "string", format: "date" }
                    }
                },
                SponsorshipOffer: {
                    type: "object",
                    properties: {
                        id: { type: "integer" },
                        sponsor_name: { type: "string" },
                        offer_amount: { type: "number" },
                        status: { type: "string", enum: ["pending", "accepted", "declined", "paid"] },
                        is_paid: { type: "boolean" },
                        sponsorable_type: { type: "string" }
                    }
                }
            }
        }
    },

    apis: [
        "./src/routes/publicRoutes.js",
        "./src/routes/sponsorshipRoutes.js",
        "./src/routes/biddingRoutes.js",
        "./src/routes/winnerRoutes.js",
        "./src/routes/analyticsRoutes.js"
    ]

};

const swaggerSpec = swaggerJsDoc(swaggerOptions);

export default swaggerSpec;