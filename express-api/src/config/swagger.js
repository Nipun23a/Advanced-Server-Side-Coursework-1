const swaggerJsDoc = require('swagger-jsdoc');

const swaggerOptions = {
    definition: {
        openapi: '3.0.0',
        info: {
            title: 'Alumni Influencer API',
            version: '1.0.0',
            description: `
                RESTful API for the Alumni Influencers Platform.
                
                This API serves as the business logic layer for the Alumni Influencers platform,
                providing endpoints for blind bidding, featured alumni retrieval, API key management,
                and sponsorship operations.
                
                **Authentication:**
                - External clients: Bearer token in Authorization header
                - Internal (CodeIgniter): X-Internal-Secret header
            `,
            contact:{
                name: 'Alumni Influencers Platform',
                email: 'support@eatminster.ac.uk',
            },
        },
        servers: [
            {
                url: `http://localhost:${process.env.PORT || 3000}`,
                description: 'Development Server',
            }
        ],
        components: {
            securitySchemes: {
                BearerAuth: {
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                },
                InternalAuth: {
                    type: 'apiKey',
                    in: 'header',
                    name: 'X-Internal-Secret',
                    description: 'Shared secret for internal service-to-service communication between CodeIgniter and Express.js',
                },
            },
            schemas : {
                Error:{
                    type: 'object',
                    properties: {
                        success: { type: 'boolean', example: false },
                        error: {
                            type: 'object',
                            properties: {
                                code: { type: 'string', example: 'VALIDATION_ERROR' },
                                message: { type: 'string', example: 'Invalid input provided' },
                                details: { type: 'array', items: { type: 'object' } },
                            },
                        },
                    },
                },
                Success:{
                    type: 'object',
                    properties: {
                        success: { type: 'boolean', example: true },
                        data: { type: 'object' },
                        message: { type: 'string'},
                    },
                },
            },
            Bid:{
                type: 'object',
                properties: {
                    id: { type: 'integer', example: 1 },
                    user_id: { type: 'integer', example: 5 },
                    bid_amount: { type: 'number', format: 'decimal', example: 250.00 },
                    bid_status: { type: 'string', enum: ['active', 'won', 'lost'], example: 'active' },
                    bid_date: { type: 'string', format: 'date', example: '2026-04-01' },
                    is_cancelled: { type: 'boolean', example: false },
                    created_at: { type: 'string', format: 'date-time' },
                    updated_at: { type: 'string', format: 'date-time' },
                },
            },
            FeaturedAlumni:{
                type: 'object',
                properties: {
                    id: { type: 'integer' },
                    user_id: { type: 'integer' },
                    featured_date: { type: 'string', format: 'date' },
                    email: { type: 'string', format: 'email' },
                    bio: { type: 'string' },
                    linkedin_url: { type: 'string', format: 'uri' },
                    profile_image_url: { type: 'string' },
                    degrees: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                degree_name: { type: 'string' },
                                institution_url: { type: 'string', format: 'uri' },
                                completion_date: { type: 'string', format: 'date' },
                            },
                        },
                    },
                    certificates: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                certificate_name: { type: 'string' },
                                provider_url: { type: 'string', format: 'uri' },
                                completion_date: { type: 'string', format: 'date' },
                            },
                        },
                    },
                    licences: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                licence_name: { type: 'string' },
                                provider_url: { type: 'string', format: 'uri' },
                                completion_date: { type: 'string', format: 'date' },
                                expiry_date: { type: 'string', format: 'date', nullable: true },
                            },
                        },
                    },
                    professional_courses: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                course_name: { type: 'string' },
                                provider_url: { type: 'string', format: 'uri' },
                                completion_date: { type: 'string', format: 'date' },
                            },
                        },
                    },
                    employment_history: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                company_name: { type: 'string' },
                                role: { type: 'string' },
                                start_date: { type: 'string', format: 'date' },
                                end_date: { type: 'string', format: 'date', nullable: true },
                            },
                        },
                    },
                },
            },
            APIKey:{
                type: 'object',
                properties: {
                    id: { type: 'integer' },
                    is_active: { type: 'boolean' },
                    created_at: { type: 'string', format: 'date-time' },
                    revoked_at: { type: 'string', format: 'date-time', nullable: true },
                },
            },
            SponsorshipOffer:{
                type: 'object',
                properties: {
                    id: { type: 'integer' },
                    sponsor_name: { type: 'string' },
                    sponsor_type: { type: 'string', enum: ['course_provider', 'licensing_body', 'certification_body'] },
                    offer_amount: { type: 'number', format: 'decimal', example: 200.00 },
                    status: { type: 'string', enum: ['pending', 'accepted', 'declined', 'paid'] },
                    is_paid: { type: 'boolean' },
                    sponsorable_type: { type: 'string', enum: ['certificate', 'licence', 'professional_course'] },
                    created_at: { type: 'string', format: 'date-time' },
                },
            },
            MonthlyLimit:{
                type: 'object',
                properties: {
                    featured_count: { type: 'integer', example: 2 },
                    max_allowed: { type: 'integer', example: 3 },
                    remaining: { type: 'integer', example: 1 },
                    attended_event: { type: 'boolean', example: false },
                    year: { type: 'integer', example: 2026 },
                    month: { type: 'integer', example: 4 },
                },
            },
            BidStatus:{
                type: 'object',
                properties: {
                    bid_id: { type: 'integer' },
                    bid_date: { type: 'string', format: 'date' },
                    your_bid_amount: { type: 'number', format: 'decimal' },
                    is_winning: { type: 'boolean' },
                },
            },
        },
    },
    apis: ['./src/routes/*.js'],
};

const swaggerSpec = swaggerJsDoc(swaggerOptions);
module.exports = swaggerSpec;

