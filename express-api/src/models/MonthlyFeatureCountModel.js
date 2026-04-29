import { pool } from "../config/database.js";

class MonthlyFeatureCountModel {
    static async findByUserAndMonth(userId, year, month) {
        const [rows] = await pool.execute(
            'SELECT count, attended_event FROM monthly_feature_counts WHERE user_id = ? AND year = ? AND month = ?',
            [userId, year, month]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async getCurrentMonthCount(userId) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;

        const record = await this.findByUserAndMonth(userId, year, month);
        return {
            count: record ? record.count : 0,
            attended_event: record ? Boolean(record.attended_event):false,
            year,
            month,
        };
    }

    static async increamentCount(userId, year, month, connection = null) {
        const db = connection || pool;
        const [existingRows] = await db.execute(
            `SELECT id
             FROM monthly_feature_counts
             WHERE user_id = ? AND year = ? AND month = ?
             ORDER BY id DESC
             LIMIT 1`,
            [userId, year, month]
        );

        if (existingRows.length > 0) {
            const [result] = await db.execute(
                `UPDATE monthly_feature_counts
                 SET count = count + 1
                 WHERE id = ?`,
                [existingRows[0].id]
            );
            return result.affectedRows > 0;
        }

        const [result] = await db.execute(
            `INSERT INTO monthly_feature_counts (user_id, year, month, count, attended_event)
             VALUES (?, ?, ?, 1, false)`,
            [userId, year, month]
        );
        return result.affectedRows > 0;
    }

    static async markEventAttended(userId, year, month, connection = null) {
        const db = connection || pool;
        const [existingRows] = await db.execute(
            `SELECT id
             FROM monthly_feature_counts
             WHERE user_id = ? AND year = ? AND month = ?
             ORDER BY id DESC
             LIMIT 1`,
            [userId, year, month]
        );

        if (existingRows.length > 0) {
            const [result] = await db.execute(
                `UPDATE monthly_feature_counts
                 SET attended_event = true
                 WHERE id = ?`,
                [existingRows[0].id]
            );
            return result.affectedRows > 0;
        }

        const [result] = await db.execute(
            `INSERT INTO monthly_feature_counts (user_id, year, month, count, attended_event)
             VALUES (?, ?, ?, 0, true)`,
            [userId, year, month]
        );
        return result.affectedRows > 0;
    }

    static async checkLimit(userId, year, month, normalLimit = 3, eventLimit = 4){
        const record = await this.findByUserAndMonth(userId, year, month);
        
        const count = record ? record.count : 0;
        const attendedEvent = record ? Boolean(record.attended_event) : false;

        const maxAllowed = attendedEvent ? eventLimit : normalLimit;
        const remaining = Math.max(0, maxAllowed - count);

        return {
            hasReachedLimit: count >= maxAllowed,
            count,
            remaining,
            attendedEvent,
        };
    }
}
export default MonthlyFeatureCountModel;
