import { pool } from "../config/database.js";

class AlumniEventModel {
    static async create(profileId, eventName, eventDate, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            `INSERT INTO alumni_events (profile_id, event_name, event_date, created_at)
             VALUES (?, ?, ?, NOW())`,
            [profileId, eventName, eventDate]
        );
        return result;
    }

    static async findProfileIdByUserId(userId) {
        const [rows] = await pool.execute(
            'SELECT id FROM alumni_profiles WHERE user_id = ? LIMIT 1',
            [userId]
        );
        return rows.length > 0 ? rows[0].id : null;
    }

    static async findByProfileAndMonth(profileId, year, month) {
        const [rows] = await pool.execute(
            `SELECT id, profile_id, event_name, event_date, created_at
             FROM alumni_events
             WHERE profile_id = ?
               AND YEAR(event_date) = ?
               AND MONTH(event_date) = ?
             ORDER BY event_date DESC, id DESC`,
            [profileId, year, month]
        );
        return rows;
    }
}

export default AlumniEventModel;
