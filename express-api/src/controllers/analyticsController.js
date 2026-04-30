import AnalyticsService from "../services/AnalyticsService";
import { sendSuccess, sendError } from "../utils/responseHelper";

import { logger } from "../config/logger";

class AnalyticsController {
    static async getSummary(req, res) {
        try {
            const result = await AnalyticsService.getDashboardSummary();
            return sendSuccess(res, result, 'Dashboard summary retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getSummary error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load summary.', 500);
        }
    }

    static async getFilterOptions(req, res) {
        try {
            const result = await AnalyticsService.getFilterOptions();
            return sendSuccess(res, result, 'Filter options retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getFilterOptions error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load filter options.', 500);
        }
    }

    static async getAllCharts(req, res) {
        try {
            const result = await AnalyticsService.getAllCharts(req.query);
            return sendSuccess(res, result, 'All chart data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getAllCharts error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load chart data.', 500);
        }
    }

    static async getSkillsGap(req, res) {
        try {
            const result = await AnalyticsService.getSkillsGap(req.query);
            return sendSuccess(res, result, 'Skills gap data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getSkillsGap error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load skills gap data.', 500);
        }
    }

    static async getEmploymentSectors(req, res) {
        try {
            const result = await AnalyticsService.getEmploymentBySector(req.query);
            return sendSuccess(res, result, 'Employment sectors data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getEmploymentSectors error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load employment data.', 500);
        }
    }

    static async getJobTitles(req, res) {
        try {
            const result = await AnalyticsService.getTopJobTitles(req.query);
            return sendSuccess(res, result, 'Job titles data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getJobTitles error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load job titles.', 500);
        }
    }

    static async getTopEmployers(req, res) {
        try {
            const result = await AnalyticsService.getTopEmployers(req.query);
            return sendSuccess(res, result, 'Top employers data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getTopEmployers error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load employers data.', 500);
        }
    }

    static async getCertificationTrends(req, res) {
        try {
            const result = await AnalyticsService.getCertificationTrends(req.query);
            return sendSuccess(res, result, 'Certification trends retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getCertificationTrends error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load certification trends.', 500);
        }
    }

    static async getLicenseDistribution(req, res) {
        try {
            const result = await AnalyticsService.getLicenseDistribution(req.query);
            return sendSuccess(res, result, 'License distribution retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getLicenseDistribution error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load license data.', 500);
        }
    }

    static async getCareerPathways(req, res) {
        try {
            const result = await AnalyticsService.getCareerPathways(req.query);
            return sendSuccess(res, result, 'Career pathways data retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getCareerPathways error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load career pathways.', 500);
        }
    }

    static async getGraduationOutcomes(req, res) {
        try {
            const result = await AnalyticsService.getGraduationOutcomes();
            return sendSuccess(res, result, 'Graduation outcomes retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.getGraduationOutcomes error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load graduation outcomes.', 500);
        }
    }


    static async browseAlumni(req, res) {
        try {
            const result = await AnalyticsService.getAlumniBrowse(req.query);
            return sendSuccess(res, result, 'Alumni list retrieved.');
        } catch (error) {
            logger.error('AnalyticsController.browseAlumni error:', error);
            return sendError(res, 'ANALYTICS_ERROR', 'Failed to load alumni list.', 500);
        }
    }
}

export default AnalyticsController;