import { pool } from "../config/database.js";

class FeatureAlumniModel {
    static async create(userId, bidId, featuredDate, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            'INSERT INTO featured_alumni (user_id, bid_id, featured_at, created_at) VALUES (?, ?, ?, NOW())',
            [userId, bidId, featuredDate]
        );
        return result;
    }

    static async findByDate(date) {
        const [rows] = await pool.execute(
            `SELECT fa.id, fa.featured_at, fa.user_id,
                    u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE fa.featured_at = ?`,
            [date]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findByDateWithBidDetails(date) {
        const [rows] = await pool.execute(
            `SELECT fa.id, fa.featured_at, fa.user_id, fa.bid_id,
                    b.bid_amount, b.bid_date, b.created_at AS bid_created_at,
                    u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE fa.featured_at = ?`,
            [date]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async fetchAllCredentials(userId) {
        const [degrees, certificates, licenses, courses, employment] = await Promise.all([
            pool.execute(
                'SELECT id, degree_name, institution_url, completion_date FROM degrees WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, certificate_name, issuer_name AS provider_url, completion_date FROM certificates WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, license_name, license_url AS provider_url, completion_date, expiration_date AS expiry_date FROM licenses WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, course_name, provider_url, completion_date, NULL AS end_date FROM professional_courses WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, company_name, industry_sector, job_title AS role, start_date, end_date FROM employment_history WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
        ]);
        return {
            degrees: degrees[0],
            certificates: certificates[0],
            licences: licenses[0],
            professional_courses: courses[0],
            employment_history: employment[0],
        };
    }

    static async findHistory(limit, offset) {
        const safeLimit = Number.isInteger(limit) && limit > 0 ? limit : 10;
        const safeOffset = Number.isInteger(offset) && offset >= 0 ? offset : 0;

        const [rows] = await pool.query(
            `SELECT fa.featured_at, u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             ORDER BY fa.featured_at DESC
              LIMIT ${safeOffset}, ${safeLimit}`

        );
        return rows;
    }

    static async findWinnersHistory(limit, offset) {
        const safeLimit = Number.isInteger(limit) && limit > 0 ? limit : 10;
        const safeOffset = Number.isInteger(offset) && offset >= 0 ? offset : 0;

        const [rows] = await pool.query(
            `SELECT fa.featured_at, fa.user_id, fa.bid_id,
                    b.bid_amount, u.email
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             ORDER BY fa.featured_at DESC
              LIMIT ${safeOffset}, ${safeLimit}`

        );
        return rows;
    }

    static async countAll() {
        const [rows] = await pool.execute(
            'SELECT COUNT(*) AS total FROM featured_alumni'
        );
        return rows[0].total;
    }

    static async findCandidateBids(bidDate, connection) {
        const [rows] = await connection.execute(
            `SELECT b.id, b.user_id, b.bid_amount, b.created_at, u.email
             FROM bids b
             JOIN users u ON b.user_id = u.id
             WHERE b.bid_date = ? AND b.bid_status = 'active' AND b.is_cancelled = false
             ORDER BY b.bid_amount DESC, b.created_at ASC`,
            [bidDate]
        );
        return rows;
    }

    static async getMonthlyCount(userId, year, month, connection) {
        const [rows] = await connection.execute(
            `SELECT count, attended_event FROM monthly_feature_counts
             WHERE user_id = ? AND year = ? AND month = ?`,
            [userId, year, month]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async markBidAsWon(bidId, connection) {
        await connection.execute(
            "UPDATE bids SET bid_status = 'won', updated_at = NOW() WHERE id = ?",
            [bidId]
        );
    }

    static async markOtherBidsAsLost(bidDate, winnerBidId, connection) {
        const [result] = await connection.execute(
            `UPDATE bids SET bid_status = 'lost', updated_at = NOW()
             WHERE bid_date = ? AND id != ? AND bid_status = 'active' AND is_cancelled = false`,
            [bidDate, winnerBidId]
        );
        return result.affectedRows;
    }

    static async incrementMonthlyCount(userId, year, month, connection) {
        const [existingRows] = await connection.execute(
            `SELECT id
             FROM monthly_feature_counts
             WHERE user_id = ? AND year = ? AND month = ?
             ORDER BY id DESC
             LIMIT 1`,
            [userId, year, month]
        );

        if (existingRows.length > 0) {
            await connection.execute(
                `UPDATE monthly_feature_counts
                 SET count = count + 1
                 WHERE id = ?`,
                [existingRows[0].id]
            );
            return;
        }

        await connection.execute(
            `INSERT INTO monthly_feature_counts (user_id, year, month, count, attended_event)
             VALUES (?, ?, ?, 1, false)`,
            [userId, year, month]
        );
    }

    static async findAlumniProfileIdByUserId(userId, connection) {
        const [rows] = await connection.execute(
            'SELECT id FROM alumni_profiles WHERE user_id = ? LIMIT 1',
            [userId]
        );
        return rows.length > 0 ? rows[0].id : null;
    }

    static async existsByDate(date) {
        const [rows] = await pool.execute(
            'SELECT id FROM featured_alumni WHERE featured_at = ?',
            [date]
        );
        return rows.length > 0;
    }
}

export default FeatureAlumniModel;
