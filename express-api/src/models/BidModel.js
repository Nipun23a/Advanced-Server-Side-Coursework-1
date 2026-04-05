import { pool } from "../config/database.js";

class BidModel {

    static async create(bidData) {
        const { user_id, bid_amount, bid_date } = bidData;

        const [result] = await pool.execute(
            `INSERT INTO bids
             (user_id, bid_amount, bid_status, bid_date, is_cancelled, created_at, updated_at)
             VALUES (?, ?, 'active', ?, false,NOW(), NOW())`,
            [user_id, bid_amount, bid_date]
        );
        return result;
    }

    static async findById(bidId) {
        const [rows] = await pool.execute(
            `SELECT * FROM bids WHERE id = ?`,
            [bidId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findByIdAndUser(bidId, userId) {
        const [rows] = await pool.execute(
            `SELECT * FROM bids WHERE id = ? AND user_id = ?`,
            [bidId, userId]
        );
        return rows.length > 0 ? rows[0] : null;
    }
    static async findActiveByUserAndDate(userId, bidDate) {
        const [rows] = await pool.execute(
            `SELECT * FROM bids
             WHERE user_id = ? AND bid_date = ? AND is_cancelled = false`,
            [userId, bidDate]
        );
        return rows.length > 0 ? rows[0] : null;
    }
    static async findLatestActiveBid(userId) {
        const [rows] = await pool.execute(
            `SELECT *
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

    static async findHistoryByUser(userId, limit, offset) {
        const safeLimit = Number.isInteger(limit) && limit > 0 ? limit : 10;
        const safeOffset = Number.isInteger(offset) && offset >= 0 ? offset : 0;

        const [rows] = await pool.query(
            `SELECT id, bid_amount, bid_status, bid_date, is_cancelled, created_at, updated_at
             FROM bids WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ${safeOffset}, ${safeLimit}`,
            [userId]
        );
        return rows;
    }

    static async countByUser(userId) {
        const [rows] = await pool.execute(
            'SELECT COUNT(*) AS total FROM bids WHERE user_id = ?',
            [userId]
        );
        return rows[0].total;
    }

    static async getMonthlyCount(userId, year, month) {
        const [rows] = await pool.execute(
            `SELECT count, attended_event FROM monthly_feature_counts
             WHERE user_id = ? AND year = ? AND month = ?`,
            [userId, year, month]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async getAvailableSponsorshipBalance(userId) {
        const [rows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance
             FROM sponsorship_offers so
             JOIN alumni_profiles ap ON so.alumni_id = ap.id
             WHERE ap.user_id = ? AND so.status = 'accepted' AND so.is_paid = false`,
            [userId]
        );
        return parseFloat(rows[0].available_balance);
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
}

export default BidModel;
