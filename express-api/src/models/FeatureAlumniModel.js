import {pool} from "../config/database.js";

class FeatureAlumniModel {
    static async create(userId, bidId, featuredDate,connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            'INSERT INTO featured_alumni (user_id, bid_id, featured_at, created_at) VALUES (?, ?,?,NOW())',
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

    static async fetchAllCredentials(userId){
        const [degrees,certificates,licences,courses,employment] = await Promise.all([
            pool.execute(
                'SELECT id, degree_name, institution_url, completion_date FROM degrees WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, certificate_name, provider_url, completion_date FROM certificates WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, license_name, provider_url, completion_date, expiry_date FROM licences WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, course_name, provider_url, completion_date, end_date FROM professional_courses WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
            pool.execute(
                'SELECT id, company_name, role, start_date, end_date FROM employment_history WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [userId]
            ),
        ]);
        return {
            degrees: degrees[0],
            certificates: certificates[0],
            licences: licences[0],
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
            `SELECT b.id, b.user_id, b.bid_amount, b.created_at
             FROM bids b
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
        await connection.execute(
            `INSERT INTO monthly_feature_counts (user_id, year, month, count, attended_event)
             VALUES (?, ?, ?, 1, false)
             ON DUPLICATE KEY UPDATE count = count + 1`,
            [userId, year, month]
        );
    }

    static async markSponsorshipsPaid(userId, connection) {
        const [result] = await connection.execute(
            `UPDATE sponsorship_offers
             SET is_paid = true, status = 'paid', updated_at = NOW()
             WHERE alumni_id = (SELECT id FROM alumni_profiles WHERE user_id = ? LIMIT 1)
             AND status = 'accepted' AND is_paid = false`,
            [userId]
        );
        return result.affectedRows;
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
