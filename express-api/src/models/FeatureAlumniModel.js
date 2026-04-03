import {pool} from "../config/database.js";

class FeatureAlumniModel {
    static async create(data,connection = null){
        const db = connection || pool;
        const {user_id,bid_id,featured_date} = data;

        const [result] = await db.execute(
            `INSERT INTO featured_alumni (user_id, bid_id, featured_date, created_at)
             VALUES (?, ?, ?, NOW())`,
            [user_id, bid_id, featured_date]
        );
        return {
            id: result.insertId,
            user_id,
            bid_id,
            featured_date,
        };
    }

    static async findByDate(date) {
        const [rows] = await pool.execute(
            'SELECT * FROM featured_alumni WHERE featured_date = ?',
            [date]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async getTodayWithProfile(){
        const today = new Date().toISOString().split('T')[0];
        const [rows] = await pool.execute(
            `SELECT fa.id, fa.featured_date, fa.user_id,
                    u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE fa.featured_date = ?`,
            [today]
        );

        if (rows.length == 0) return null;
        const alumni = rows[0];

                const [degrees, certificates, licences, courses, employment] = await Promise.all([
            pool.execute(
                'SELECT id, degree_name, institution_url, completion_date FROM degrees WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [alumni.user_id]
            ),
            pool.execute(
                'SELECT id, certificate_name, provider_url, completion_date FROM certificates WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [alumni.user_id]
            ),
            pool.execute(
                'SELECT id, licence_name, provider_url, completion_date, expiry_date FROM licences WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [alumni.user_id]
            ),
            pool.execute(
                'SELECT id, course_name, provider_url, completion_date, end_date FROM professional_courses WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [alumni.user_id]
            ),
            pool.execute(
                'SELECT id, company_name, role, start_date, end_date FROM employment_history WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)',
                [alumni.user_id]
            ),
        ]);

        return {
            featured_date : alumni.featured_date,
            email : alumni.email,
            bio: alumni.bio,
            linkedin_url:alumni.linkedin_url,
            profile_image_url:alumni.profile_image_url,
            degrees:degrees[0],
            certificates:certificates[0],
            licences:licences[0],
            professional_courses:courses[0],
            employment_history:employment[0]
        };
    }

    static async getTodayWithBidDetails(){
        const today = new Date().toISOString().split('T')[0];
        const [rows] = await pool.execute(
            `SELECT fa.id, fa.featured_date, fa.user_id, fa.bid_id,
                    b.bid_amount, b.bid_date,
                    u.email
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             WHERE fa.featured_date = ?`,
            [today]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async getHistory(page=1,limit=10){
        const offset = (page - 1) * limit;
        const [rows] = await pool.execute(
            `SELECT fa.featured_date, u.email, ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             ORDER BY fa.featured_date DESC LIMIT ? OFFSET ?`,
            [limit, offset]
        );
 
        const [countResult] = await pool.execute(
            'SELECT COUNT(*) AS total FROM featured_alumni'
        );

        return{
            featured: rows,
            total:countResult[0].total
        };
    }

    static async getWinnerHistory(page = 1,limit = 10){
        const offset = (page - 1) * limit;
 
        const [rows] = await pool.execute(
            `SELECT fa.featured_date, fa.user_id, b.bid_amount, u.email
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             ORDER BY fa.featured_date DESC LIMIT ? OFFSET ?`,
            [limit, offset]
        );
 
        const [countResult] = await pool.execute(
            'SELECT COUNT(*) AS total FROM featured_alumni'
        );
 
        return {
            winners: rows,
            total: countResult[0].total,
        };
    }
}

export default FeatureAlumniModel;