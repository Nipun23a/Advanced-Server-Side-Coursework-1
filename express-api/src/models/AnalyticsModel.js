import { pool } from '../config/database.js';

function buildDegreeFilterParts(alias, filters = {}) {
    const conditions = [];
    const params = [];

    if (filters.programme) {
        conditions.push(`${alias}.degree_name = ?`);
        params.push(filters.programme);
    }

    if (filters.graduationYear) {
        conditions.push(`YEAR(${alias}.completion_date) = ?`);
        params.push(parseInt(filters.graduationYear, 10));
    }

    return { conditions, params };
}

class AnalyticsModel {
    static async getSkillsGap(filters = {}) {
        const { conditions, params: degreeParams } = buildDegreeFilterParts('d', filters);
        const params = [...degreeParams, ...degreeParams];
        const degreeWhere = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';
        const mainFilters = conditions.length > 0 ? ` AND ${conditions.join(' AND ')}` : '';

        let query = `
            SELECT
                c.certificate_name AS label,
                COUNT(DISTINCT c.id) AS value,
                ROUND(
                    COUNT(DISTINCT c.id) * 100.0 / NULLIF((
                        SELECT COUNT(DISTINCT d.profile_id)
                        FROM degrees d
                        ${degreeWhere}
                    ), 0),
                    1
                ) AS penetration_percent
            FROM certificates c
            JOIN alumni_profiles ap ON c.profile_id = ap.id
            JOIN degrees d ON d.profile_id = ap.id
            WHERE 1=1
        `;

        query += mainFilters;
        query += `
            GROUP BY c.certificate_name
            ORDER BY value DESC
            LIMIT 15
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
            penetration: rows.map(r => r.penetration_percent),
        };
    }

    static async getEmploymentBySector(filters = {}) {
        const { conditions, params } = buildDegreeFilterParts('d', filters);

        let query = `
            SELECT
                COALESCE(NULLIF(TRIM(eh.industry_sector), ''), 'Not Specified') AS label,
                COUNT(DISTINCT ap.id) AS value
            FROM employment_history eh
            JOIN alumni_profiles ap ON eh.profile_id = ap.id
            WHERE eh.end_date IS NULL
        `;

        if (conditions.length > 0) {
            query += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                      AND ${conditions.join(' AND ')}
                )
            `;
        }

        query += `
            GROUP BY label
            ORDER BY value DESC
            LIMIT 12
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
        };
    }

    static async getTopJobTitles(filters = {}) {
        const { conditions, params } = buildDegreeFilterParts('d', filters);

        let query = `
            SELECT
                eh.job_title AS label,
                COUNT(DISTINCT eh.id) AS value
            FROM employment_history eh
            WHERE eh.job_title IS NOT NULL
              AND TRIM(eh.job_title) != ''
              AND eh.end_date IS NULL
        `;

        if (conditions.length > 0) {
            query += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = eh.profile_id
                      AND ${conditions.join(' AND ')}
                )
            `;
        }

        query += `
            GROUP BY eh.job_title
            ORDER BY value DESC
            LIMIT 15
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
        };
    }

    static async getTopEmployers(filters = {}) {
        const { limit = 10 } = filters;
        const { conditions, params: degreeParams } = buildDegreeFilterParts('d', filters);
        const params = [...degreeParams];
        const safeLimit = Math.max(1, parseInt(limit, 10) || 10);

        let query = `
            SELECT
                eh.company_name AS label,
                COUNT(DISTINCT ap.id) AS value
            FROM employment_history eh
            JOIN alumni_profiles ap ON eh.profile_id = ap.id
            WHERE eh.company_name IS NOT NULL
              AND TRIM(eh.company_name) != ''
              AND eh.end_date IS NULL
        `;

        if (conditions.length > 0) {
            query += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                      AND ${conditions.join(' AND ')}
                )
            `;
        }

        query += `
            GROUP BY eh.company_name
            ORDER BY value DESC
            LIMIT ${safeLimit}
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
        };
    }

    static async getCertificationTrends(filters = {}) {
        const { months = 24 } = filters;
        const { conditions, params: degreeParams } = buildDegreeFilterParts('d', filters);
        const params = [parseInt(months, 10), ...degreeParams];

        let query = `
            SELECT
                DATE_FORMAT(c.completion_date, '%Y-%m') AS label,
                COUNT(DISTINCT c.id) AS value
            FROM certificates c
            WHERE c.completion_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        `;

        if (conditions.length > 0) {
            query += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = c.profile_id
                      AND ${conditions.join(' AND ')}
                )
            `;
        }

        query += `
            GROUP BY label
            ORDER BY label ASC
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
        };
    }

    static async getLicenseDistribution(filters = {}) {
        const { conditions, params } = buildDegreeFilterParts('d', filters);

        let query = `
            SELECT
                l.license_name AS label,
                COUNT(DISTINCT l.id) AS value
            FROM licenses l
            WHERE l.license_name IS NOT NULL
              AND TRIM(l.license_name) != ''
        `;

        if (conditions.length > 0) {
            query += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = l.profile_id
                      AND ${conditions.join(' AND ')}
                )
            `;
        }

        query += `
            GROUP BY l.license_name
            ORDER BY value DESC
            LIMIT 10
        `;

        const [rows] = await pool.execute(query, params);

        return {
            labels: rows.map(r => r.label),
            data: rows.map(r => r.value),
        };
    }

    static async getCareerPathways(filters = {}) {
        const { graduationYear } = filters;
        const params = [];
 
        let query = `
            SELECT
                d.degree_name AS programme,
                eh.job_title AS job_title,
                COUNT(DISTINCT ap.id) AS value
            FROM degrees d
            JOIN alumni_profiles ap ON d.profile_id = ap.id
            JOIN employment_history eh ON eh.profile_id = ap.id
            WHERE eh.end_date IS NULL
              AND eh.job_title IS NOT NULL
              AND TRIM(eh.job_title) != ''
              AND d.degree_name IS NOT NULL
        `;

        if (graduationYear) {
            query += ` AND YEAR(d.completion_date) = ?`;
            params.push(parseInt(graduationYear, 10));
        }

        query += `
            GROUP BY d.degree_name, eh.job_title
            ORDER BY d.degree_name, value DESC
        `;

        const [rows] = await pool.execute(query, params);

        // Transform into grouped bar format for Chart.js
        // { programmes: [...], datasets: [{ label: jobTitle, data: [...] }] }
        const programmes = [...new Set(rows.map(r => r.programme))];
        const jobTitles = [...new Set(rows.map(r => r.job_title))].slice(0, 6);

        const datasets = jobTitles.map(title => ({
            label: title,
            data: programmes.map(prog => {
                const match = rows.find(r => r.programme === prog && r.job_title === title);
                return match ? match.value : 0;
            }),
        }));

        return { labels: programmes, datasets };
    }

    static async getGraduationOutcomes() {
        const [rows] = await pool.execute(`
            SELECT
                YEAR(d.completion_date) AS grad_year,
                COUNT(DISTINCT ap.id) AS total_alumni,
                COUNT(DISTINCT eh.profile_id) AS employed_count,
                COUNT(DISTINCT c.profile_id) AS certified_count
            FROM degrees d
            JOIN alumni_profiles ap ON d.profile_id = ap.id
            LEFT JOIN employment_history eh
                ON eh.profile_id = ap.id AND eh.end_date IS NULL
            LEFT JOIN certificates c ON c.profile_id = ap.id
            WHERE YEAR(d.completion_date) >= YEAR(CURDATE()) - 5
            GROUP BY grad_year
            ORDER BY grad_year ASC
        `);
 
        return {
            labels: rows.map(r => r.grad_year.toString()),
            total: rows.map(r => r.total_alumni),
            employed: rows.map(r => r.employed_count),
            certified: rows.map(r => r.certified_count),
        };
    }

    static async getAlumniBrowse(filters = {}) {
        const { programme, graduationYear, sector, page = 1, limit = 20 } = filters;
        const safePage = Math.max(1, parseInt(page, 10) || 1);
        const safeLimit = Math.max(1, parseInt(limit, 10) || 20);
        const offset = (safePage - 1) * safeLimit;
        const params = [];
        let whereClause = `
            FROM alumni_profiles ap
            JOIN users u ON ap.user_id = u.id
            WHERE 1=1
        `;

        const selectClause = `
            SELECT
                ap.id,
                u.email,
                ap.bio,
                ap.linkedin_url,
                ap.profile_image_url,
                (
                    SELECT d.degree_name
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                    ORDER BY d.completion_date DESC, d.id DESC
                    LIMIT 1
                ) AS programme,
                (
                    SELECT YEAR(d.completion_date)
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                    ORDER BY d.completion_date DESC, d.id DESC
                    LIMIT 1
                ) AS graduation_year,
                (
                    SELECT eh.company_name
                    FROM employment_history eh
                    WHERE eh.profile_id = ap.id
                      AND eh.end_date IS NULL
                    ORDER BY eh.start_date DESC, eh.id DESC
                    LIMIT 1
                ) AS current_employer,
                (
                    SELECT eh.job_title
                    FROM employment_history eh
                    WHERE eh.profile_id = ap.id
                      AND eh.end_date IS NULL
                    ORDER BY eh.start_date DESC, eh.id DESC
                    LIMIT 1
                ) AS current_role
        `;

        if (programme) {
            whereClause += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                      AND d.degree_name = ?
                )
            `;
            params.push(programme);
        }

        if (graduationYear) {
            whereClause += `
                AND EXISTS (
                    SELECT 1
                    FROM degrees d
                    WHERE d.profile_id = ap.id
                      AND YEAR(d.completion_date) = ?
                )
            `;
            params.push(parseInt(graduationYear, 10));
        }

        if (sector) {
            whereClause += `
                AND EXISTS (
                    SELECT 1
                    FROM employment_history eh
                    WHERE eh.profile_id = ap.id
                      AND eh.end_date IS NULL
                      AND eh.industry_sector LIKE ?
                )
            `;
            params.push(`%${sector}%`);
        }

        const query = `
            ${selectClause}
            ${whereClause}
            ORDER BY ap.id DESC
            LIMIT ${safeLimit} OFFSET ${offset}
        `;

        const [rows] = await pool.execute(query, params);

        const countQuery = `
            SELECT COUNT(*) AS total
            FROM alumni_profiles ap
            JOIN users u ON ap.user_id = u.id
            ${whereClause.replace(/^\s*FROM alumni_profiles ap\s+JOIN users u ON ap\.user_id = u\.id\s+/m, '')}
        `;
        const countParams = [...params];
        const [countRows] = await pool.execute(countQuery, countParams);

        return {
            alumni: rows,
            total: countRows[0]?.total || 0,
            page: safePage,
            limit: safeLimit,
        };
    }

    static async getProgrammes() {
        const [rows] = await pool.execute(`
            SELECT DISTINCT degree_name AS label
            FROM degrees
            WHERE degree_name IS NOT NULL
            ORDER BY degree_name ASC
        `);
        return rows.map(r => r.label);
    }

    static async getGraduationYears() {
        const [rows] = await pool.execute(`
            SELECT DISTINCT YEAR(completion_date) AS label
            FROM degrees
            WHERE completion_date IS NOT NULL
            ORDER BY label DESC
        `);
        return rows.map(r => r.label);
    }

    static async getSectors() {
        const [rows] = await pool.execute(`
            SELECT DISTINCT industry_sector AS label
            FROM employment_history
            WHERE industry_sector IS NOT NULL AND industry_sector != ''
            ORDER BY label ASC
        `);
        return rows.map(r => r.label);
    }

    static async getDashboardSummary() {
        const [
            [alumniCount],
            [certCount],
            [licenseCount],
            [topDegree],
            [topJobTitle],
        ] = await Promise.all([
            pool.execute('SELECT COUNT(*) AS total FROM alumni_profiles'),
            pool.execute('SELECT COUNT(*) AS total FROM certificates'),
            pool.execute('SELECT COUNT(*) AS total FROM licenses'),
            pool.execute(`
                SELECT degree_name AS label, COUNT(*) AS count
                FROM degrees GROUP BY degree_name ORDER BY count DESC LIMIT 1
            `),
            pool.execute(`
                SELECT job_title AS label, COUNT(*) AS count
                FROM employment_history
                WHERE end_date IS NULL AND job_title IS NOT NULL
                GROUP BY job_title ORDER BY count DESC LIMIT 1
            `),
        ]);
 
        return {
            total_alumni: alumniCount[0].total,
            total_certifications: certCount[0].total,
            total_licenses: licenseCount[0].total,
            top_programme: topDegree[0]?.label || 'N/A',
            top_job_title: topJobTitle[0]?.label || 'N/A',
        };
    }
}

export default AnalyticsModel;
