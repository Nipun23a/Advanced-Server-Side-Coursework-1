import { pool } from "../config/database.js";

class SponsorshipOfferModel {
    static async create(data) {
        const {
            sponsorship_id,
            alumni_id,
            sponsorable_id,
            sponsorable_type,
            offer_amount
        } = data;

        // Basic validation
        if (!sponsorship_id || !alumni_id || !offer_amount) {
            throw new Error("Missing required sponsorship fields");
        }

        const [result] = await pool.execute(
            `INSERT INTO sponsorship_offers 
            (sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount, status, is_paid, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'pending', false, NOW(), NOW())`,
            [sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount]
        );

        return {
            id: result.insertId,
            sponsorship_id,
            alumni_id,
            sponsorable_id,
            sponsorable_type,
            offer_amount,
            status: "pending",
            is_paid: false
        };
    }
    static async findById(offerId) {
        const [rows] = await pool.execute(
            `SELECT so.*, s.sponsor_name, s.sponsor_type, s.website_url
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsorship_id = s.id
             WHERE so.id = ?`,
            [offerId]
        );

        return rows.length ? rows[0] : null;
    }
    static async findAllByAlumni(alumniId) {
        const [rows] = await pool.execute(
            `SELECT so.*, s.sponsor_name, s.sponsor_type, s.website_url
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsorship_id = s.id
             WHERE so.alumni_id = ?
             ORDER BY so.created_at DESC`,
            [alumniId]
        );

        return rows;
    }
    static async updateStatus(offerId, status) {
        const [result] = await pool.execute(
            `UPDATE sponsorship_offers 
             SET status = ?, updated_at = NOW()
             WHERE id = ?`,
            [status, offerId]
        );

        return result.affectedRows > 0;
    }
    static async markAsPaid(offerId) {
        const [result] = await pool.execute(
            `UPDATE sponsorship_offers 
             SET is_paid = true, status = 'paid', updated_at = NOW()
             WHERE id = ?`,
            [offerId]
        );

        return result.affectedRows > 0;
    }
    static async getAvailableBalance(alumniId) {
        const [rows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance
             FROM sponsorship_offers 
             WHERE alumni_id = ? 
             AND status = 'accepted' 
             AND is_paid = false`,
            [alumniId]
        );

        return parseFloat(rows[0].available_balance);
    }
    static async getBalanceDetails(alumniId) {
        const [balanceRows] = await pool.execute(
            `SELECT 
                COALESCE(SUM(offer_amount), 0) AS available_balance,
                COUNT(*) AS total_accepted
             FROM sponsorship_offers
             WHERE alumni_id = ? 
             AND status = 'accepted' 
             AND is_paid = false`,
            [alumniId]
        );

        const [paidRows] = await pool.execute(
            `SELECT COUNT(*) AS total_paid
             FROM sponsorship_offers 
             WHERE alumni_id = ? 
             AND is_paid = true`,
            [alumniId]
        );

        return {
            available_balance: parseFloat(balanceRows[0].available_balance),
            total_accepted: balanceRows[0].total_accepted,
            total_paid: paidRows[0].total_paid
        };
    }
    static async markAllAsPaidForAlumni(alumniId, connection = null) {
        const db = connection || pool;

        const [result] = await db.execute(
            `UPDATE sponsorship_offers 
             SET is_paid = true, status = 'paid', updated_at = NOW()
             WHERE alumni_id = ? 
             AND status = 'accepted' 
             AND is_paid = false`,
            [alumniId]
        );

        return result.affectedRows;
    }
}

export default SponsorshipOfferModel;