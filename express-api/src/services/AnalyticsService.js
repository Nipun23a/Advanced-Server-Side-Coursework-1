import AnalyticsModel from '../models/AnalyticsModel.js';
class AnalyticsService {
    static sanitiseFilters(rawFilters = {}) {
        const { programme, graduationYear, sector, limit, months, page } = rawFilters;
 
        return {
            programme: programme ? String(programme).trim().substring(0, 100) : null,
            graduationYear: graduationYear ? parseInt(graduationYear) : null,
            sector: sector ? String(sector).trim().substring(0, 100) : null,
            limit: limit ? Math.min(parseInt(limit) || 10, 50) : 10,
            months: months ? Math.min(parseInt(months) || 24, 60) : 24,
            page: page ? Math.max(parseInt(page) || 1, 1) : 1,
        };
    }

    static async getSkillsGap(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getSkillsGap(filters);
    }

    static async getEmploymentBySector(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getEmploymentBySector(filters);
    }

    static async getTopJobTitles(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getTopJobTitles(filters);
    }
    static async getTopEmployers(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getTopEmployers(filters);
    }
    static async getCertificationTrends(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getCertificationTrends(filters);
    }
    static async getLicenseDistribution(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getLicenseDistribution(filters);
    }
 
    static async getCareerPathways(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getCareerPathways(filters);
    }
 
    static async getGraduationOutcomes() {
        return await AnalyticsModel.getGraduationOutcomes();
    }
    static async getAllCharts(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
 
        const [
            skillsGap,
            employmentSectors,
            jobTitles,
            topEmployers,
            certTrends,
            licenses,
            careerPathways,
            graduationOutcomes,
        ] = await Promise.all([
            AnalyticsModel.getSkillsGap(filters),
            AnalyticsModel.getEmploymentBySector(filters),
            AnalyticsModel.getTopJobTitles(filters),
            AnalyticsModel.getTopEmployers(filters),
            AnalyticsModel.getCertificationTrends(filters),
            AnalyticsModel.getLicenseDistribution(filters),
            AnalyticsModel.getCareerPathways(filters),
            AnalyticsModel.getGraduationOutcomes(),
        ]);
 
        return {
            skills_gap: skillsGap,
            employment_sectors: employmentSectors,
            job_titles: jobTitles,
            top_employers: topEmployers,
            certification_trends: certTrends,
            license_distribution: licenses,
            career_pathways: careerPathways,
            graduation_outcomes: graduationOutcomes,
        };
    }
    static async getAlumniBrowse(rawFilters) {
        const filters = this.sanitiseFilters(rawFilters);
        return await AnalyticsModel.getAlumniBrowse(filters);
    }
    static async getFilterOptions() {
        const [programmes, years, sectors] = await Promise.all([
            AnalyticsModel.getProgrammes(),
            AnalyticsModel.getGraduationYears(),
            AnalyticsModel.getSectors(),
        ]);
 
        return { programmes, years, sectors };
    }
    static async getDashboardSummary() {
        return await AnalyticsModel.getDashboardSummary();
    }
}

export default AnalyticsService;




