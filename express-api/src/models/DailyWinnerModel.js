import { pool } from "../config/database.js";

class DailyWinnerModel {
    static async create(userId, bidId, winnerDate, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            `INSERT INTO daily_winners (user_id, bid_id, winner_date, created_at)
             VALUES (?, ?, ?, NOW())`,
            [userId, bidId, winnerDate]
        );
        return result;
    }

    static async existsByDate(winnerDate) {
        const [rows] = await pool.execute(
            'SELECT id FROM daily_winners WHERE winner_date = ? LIMIT 1',
            [winnerDate]
        );
        return rows.length > 0;
    }

    static async findHistory(limit, offset) {
        const safeLimit = Number.isInteger(limit) && limit > 0 ? limit : 10;
        const safeOffset = Number.isInteger(offset) && offset >= 0 ? offset : 0;

        const [rows] = await pool.query(
            `SELECT dw.winner_date AS featured_at, dw.user_id, dw.bid_id,
                    b.bid_amount, u.email
             FROM daily_winners dw
             JOIN bids b ON dw.bid_id = b.id
             JOIN users u ON dw.user_id = u.id
             ORDER BY dw.winner_date DESC
             LIMIT ${safeOffset}, ${safeLimit}`
        );

        return rows;
    }

    static async countAll() {
        const [rows] = await pool.execute('SELECT COUNT(*) AS total FROM daily_winners');
        return rows[0].total;
    }
}

export default DailyWinnerModel;
