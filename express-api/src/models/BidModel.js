import { pool } from "../config/database.js";

class BidModel {

    static async create(bidData) {
        const { user_id, bid_amount, bid_date, sponsorship_total } = bidData;

        const [result] = await pool.execute(
            `INSERT INTO bids
             (user_id, bid_amount, bid_status, bid_date, is_cancelled, sponsorship_total, created_at, updated_at)
             VALUES (?, ?, 'active', ?, false, ?, NOW(), NOW())`,
            [user_id, bid_amount, bid_date, sponsorship_total]
        );

        return {
            id: result.insertId,
            user_id,
            bid_amount,
            bid_status: 'active',
            bid_date,
            is_cancelled: false,
            sponsorship_total,
        };
    }

    static async findById(bidId) {
        const [rows] = await pool.execute(
            `SELECT * FROM bids WHERE id = ?`,
            [bidId]
        );
        return rows[0] || null;
    }

    static async findByIdAndUser(bidId, userId) {
        const [rows] = await pool.execute(
            `SELECT * FROM bids WHERE id = ? AND user_id = ?`,
            [bidId, userId]
        );
        return rows[0] || null;
    }

    static async findLatestActiveBid(userId) {
        const [rows] = await pool.execute(
            `SELECT id, bid_amount, bid_date
             FROM bids
             WHERE user_id = ? AND bid_status = 'active' AND is_cancelled = false
             ORDER BY bid_date DESC
                 LIMIT 1`,
            [userId]
        );
        return rows[0] || null;
    }

    static async getHighestBidForDate(bidDate) {
        const [rows] = await pool.execute(
            `SELECT MAX(bid_amount) AS highest_amount
             FROM bids
             WHERE bid_date = ? AND bid_status = 'active' AND is_cancelled = false`,
            [bidDate]
        );
        return rows[0].highest_amount ? parseFloat(rows[0].highest_amount) : 0;
    }

    static async findCandidatesForDate(bidDate) {
        const [rows] = await pool.execute(
            `SELECT id, user_id, bid_amount, created_at
             FROM bids
             WHERE bid_date = ? AND bid_status = 'active' AND is_cancelled = false
             ORDER BY bid_amount DESC, created_at ASC`,
            [bidDate]
        );
        return rows;
    }

    static async updateAmount(bidId, newAmount) {
        const [result] = await pool.execute(
            `UPDATE bids 
             SET bid_amount = ?, updated_at = NOW() 
             WHERE id = ?`,
            [newAmount, bidId]
        );
        return result.affectedRows > 0;
    }

    static async cancel(bidId) {
        const [result] = await pool.execute(
            `UPDATE bids 
             SET is_cancelled = true, updated_at = NOW() 
             WHERE id = ?`,
            [bidId]
        );
        return result.affectedRows > 0;
    }

    static async markAsWon(bidId, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            `UPDATE bids 
             SET bid_status = 'won', updated_at = NOW() 
             WHERE id = ?`,
            [bidId]
        );
        return result.affectedRows > 0;
    }

    static async markOthersAsLost(bidDate, winnerBidId, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            `UPDATE bids
             SET bid_status = 'lost', updated_at = NOW()
             WHERE bid_date = ? AND id != ? 
             AND bid_status = 'active' AND is_cancelled = false`,
            [bidDate, winnerBidId]
        );
        return result.affectedRows;
    }

    static async getHistoryByUser(userId, page = 1, limit = 10) {
        const offset = (page - 1) * limit;

        const [bids] = await pool.execute(
            `SELECT id, bid_amount, bid_status, bid_date, is_cancelled, created_at, updated_at
             FROM bids
             WHERE user_id = ?
             ORDER BY created_at DESC
                 LIMIT ? OFFSET ?`,
            [userId, limit, offset]
        );

        const [count] = await pool.execute(
            `SELECT COUNT(*) AS total FROM bids WHERE user_id = ?`,
            [userId]
        );

        return {
            bids,
            total: count[0].total,
        };
    }
}

export default BidModel;