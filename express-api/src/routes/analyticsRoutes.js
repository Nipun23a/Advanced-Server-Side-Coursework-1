import express from 'express';
import { authenticateBearer, authenticateAny } from '../middleware/authMiddleware.js';
import { requirePermission } from '../middleware/permissionMiddleware.js';
import AnalyticsController from '../controllers/analyticsController.js';

const router = express.Router();

// ============================================================================
// ANALYTICS ROUTES — require read:analytics permission
// ============================================================================

/**
 * @swagger
 * /api/v1/analytics/summary:
 *   get:
 *     summary: Get dashboard summary statistics
 *     description: Returns summary card data — total alumni, certifications, licenses, top programme and job title.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Summary statistics
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 total_alumni: 245
 *                 total_certifications: 892
 *                 total_licenses: 156
 *                 top_programme: "Computer Science"
 *                 top_job_title: "Software Engineer"
 *       403:
 *         description: API key does not have read:analytics permission
 */
router.get(
    '/summary',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getSummary
);

/**
 * @swagger
 * /api/v1/analytics/filters:
 *   get:
 *     summary: Get filter dropdown options
 *     description: Returns available programmes, graduation years, and industry sectors for filter dropdowns.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Filter options
 */
router.get(
    '/filters',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getFilterOptions
);

/**
 * @swagger
 * /api/v1/analytics/all:
 *   get:
 *     summary: Get all 8 chart datasets in one request
 *     description: Returns data for all charts simultaneously. Filtered by programme and/or graduation year.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *         example: "Computer Science"
 *       - in: query
 *         name: graduationYear
 *         schema:
 *           type: integer
 *         example: 2023
 *     responses:
 *       200:
 *         description: All chart data
 *       403:
 *         description: Insufficient permissions
 */
router.get(
    '/all',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getAllCharts
);

/**
 * @swagger
 * /api/v1/analytics/skills-gap:
 *   get:
 *     summary: Chart 1 — Curriculum skills gap analysis
 *     description: >
 *       Returns top 15 certifications acquired by alumni post-graduation.
 *       High count = skill the curriculum does not cover adequately.
 *       Includes penetration percentage for colour-coded insights
 *       (red >70%, orange >40%, yellow >20%).
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *       - in: query
 *         name: graduationYear
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Skills gap data for bar + radar chart
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 labels: ["AWS Certified", "Docker", "Kubernetes"]
 *                 data: [87, 65, 43]
 *                 penetration: [73.1, 54.6, 36.1]
 */
router.get(
    '/skills-gap',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getSkillsGap
);

/**
 * @swagger
 * /api/v1/analytics/employment-sectors:
 *   get:
 *     summary: Chart 2 — Employment by industry sector
 *     description: Returns distribution of alumni across industry sectors for pie/doughnut chart.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *       - in: query
 *         name: graduationYear
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: Sector distribution data
 */
router.get(
    '/employment-sectors',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getEmploymentSectors
);

/**
 * @swagger
 * /api/v1/analytics/job-titles:
 *   get:
 *     summary: Chart 3 — Most common job titles
 *     description: Returns top 15 current job titles held by alumni for horizontal bar chart.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Job title frequency data
 */
router.get(
    '/job-titles',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getJobTitles
);

/**
 * @swagger
 * /api/v1/analytics/top-employers:
 *   get:
 *     summary: Chart 4 — Top N employers
 *     description: Returns companies employing the most alumni. Default top 10.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *           default: 10
 *     responses:
 *       200:
 *         description: Top employer data
 */
router.get(
    '/top-employers',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getTopEmployers
);

/**
 * @swagger
 * /api/v1/analytics/certification-trends:
 *   get:
 *     summary: Chart 5 — Certification trends over time
 *     description: >
 *       Returns monthly certification completion counts over the past N months.
 *       Rising trend signals increasing industry demand for that skill.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: months
 *         schema:
 *           type: integer
 *           default: 24
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *     responses:
 *       200:
 *         description: Monthly certification trend data for line chart
 */
router.get(
    '/certification-trends',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getCertificationTrends
);

/**
 * @swagger
 * /api/v1/analytics/license-distribution:
 *   get:
 *     summary: Chart 6 — Professional license distribution
 *     description: Returns types of licenses held by alumni for polar area chart.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: License distribution data
 */
router.get(
    '/license-distribution',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getLicenseDistribution
);

/**
 * @swagger
 * /api/v1/analytics/career-pathways:
 *   get:
 *     summary: Chart 7 — Career pathways (degree to job title)
 *     description: Returns grouped bar data showing what jobs each degree programme leads to.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Career pathway grouped bar data
 */
router.get(
    '/career-pathways',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getCareerPathways
);

/**
 * @swagger
 * /api/v1/analytics/graduation-outcomes:
 *   get:
 *     summary: Chart 8 — Graduation outcomes over time
 *     description: Returns stacked bar data showing total alumni, employed count, and certified count per graduation year.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     responses:
 *       200:
 *         description: Graduation outcomes stacked bar data
 */
router.get(
    '/graduation-outcomes',
    authenticateAny,
    requirePermission('read:analytics'),
    AnalyticsController.getGraduationOutcomes
);

// ============================================================================
// ALUMNI BROWSE ROUTE — /api/v1/alumni/browse
// Exported separately but defined here for convenience
// ============================================================================

/**
 * @swagger
 * /api/v1/alumni/browse:
 *   get:
 *     summary: Browse and filter alumni
 *     description: >
 *       Paginated alumni list with filters for programme, graduation year,
 *       and industry sector. Used by the alumni browser page with DataTables.
 *     tags: [Analytics]
 *     security:
 *       - BearerAuth: []
 *     parameters:
 *       - in: query
 *         name: programme
 *         schema:
 *           type: string
 *       - in: query
 *         name: graduationYear
 *         schema:
 *           type: integer
 *       - in: query
 *         name: sector
 *         schema:
 *           type: string
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *           default: 1
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *           default: 20
 *     responses:
 *       200:
 *         description: Filtered alumni list with pagination
 *         content:
 *           application/json:
 *             example:
 *               success: true
 *               data:
 *                 alumni: []
 *                 total: 245
 *                 page: 1
 *                 limit: 20
 */
export const alumniRouter = express.Router();
alumniRouter.get(
    '/browse',
    authenticateAny,
    requirePermission('read:alumni'),
    AnalyticsController.browseAlumni
);

export default router;