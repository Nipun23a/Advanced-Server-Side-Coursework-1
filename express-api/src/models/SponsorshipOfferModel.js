import {pool} from "../config/database.js";

class SponsorshipOfferModel {
    static async create(data) {
        const { sponsor_id, user_id, sponsorable_id, sponsorable_type, offer_amount } = data;
 
        const [result] = await pool.execute(
            `INSERT INTO sponsorship_offers 
             (sponsor_id, user_id, sponsorable_id, sponsorable_type, offer_amount, status, is_paid, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'pending', false, NOW(), NOW())`,
            [sponsor_id, user_id, sponsorable_id, sponsorable_type, offer_amount]
        );
 
        return {
            id: result.insertId,
            sponsor_id,
            user_id,
            sponsorable_id,
            sponsorable_type,
            offer_amount,
            status: 'pending',
            is_paid: false,
        };                  
    }

    static async findById(offerId) {
        const [rows] = await pool.execute(
            'SELECT * FROM sponsorship_offers WHERE id = ?',
            [offerId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findAllByUser(userId) {
        const [rows] = await pool.execute(
            `SELECT so.id, so.offer_amount, so.status, so.is_paid, 
                    so.sponsorable_type, so.sponsorable_id,
                    s.sponsor_name, s.sponsor_type, s.website_url,
                    so.created_at, so.updated_at
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsor_id = s.id
             WHERE so.user_id = ?
             ORDER BY so.created_at DESC`,
            [userId]
        );
        return rows;
    }

    static async updateStatus(offerId, status){
        const fields = [result] = await pool.execute(
            'UPDATE sponsorship_offers SET status = ?, updated_at = NOW() WHERE id = ?',
            [status, offerId]
        );
        return result.affectedRows > 0;
    }

    static async markAsPaid(offerId){
            const [rows] = await pool.execute(
                `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance
                FROM sponsorship_offers 
                WHERE user_id = ? AND status = 'accepted' AND is_paid = false`,
                [userId]
        );
        return parseFloat(rows[0].available_balance);
    }

    static async getBalanceDetails(userId){
        const [balanceRows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance,
                    COUNT(*) AS total_accepted
             FROM sponsorship_offers
             WHERE user_id = ? AND status = 'accepted' AND is_paid = false`,
            [userId]
        );
        const [paidRows] = await pool.execute(
            'SELECT COUNT(*) AS total_paid FROM sponsorship_offers WHERE user_id = ? AND is_paid = true',
            [userId]
        );
        return {
            available_balance: parseFloat(balanceRows[0].available_balance),
            total_accepted: balanceRows[0].total_accepted,
            total_paid: paidRows[0].total_paid,
        };
    }

    static async markAsPaidForUser(userId,connection = null){
        const db = connection || pool;
        const [result] = await db.execute(
            `UPDATE sponsorship_offers 
             SET is_paid = true, status = 'paid', updated_at = NOW()
             WHERE user_id = ? AND status = 'accepted' AND is_paid = false`,
            [userId]
        );

        return result.affectedRows;

    }
}

export default SponsorshipOfferModel;