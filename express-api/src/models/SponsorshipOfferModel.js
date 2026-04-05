import { pool } from '../config/database.js';

class SponsorshipOfferModel {
    static async create(data) {
        const { sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount } = data;

        if (!sponsorship_id || !alumni_id || !offer_amount) {
            throw new Error('Missing required sponsorship fields');
        }

        const [result] = await pool.execute(
            `INSERT INTO sponsorship_offers
             (sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount, status, is_paid, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'pending', false, NOW(), NOW())`,
            [sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount]
        );

        return result;
    }
    static async findById(offerId) {
        const [rows] = await pool.execute(
            `SELECT so.*, s.sponsor_name, s.sponsor_type, s.website_url
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsorship_id = s.id
             WHERE so.id = ?`,
            [offerId]
        );
        return rows.length > 0 ? rows[0] : null;
    }
    static async findByIdAndAlumni(offerId, alumniId) {
        const [rows] = await pool.execute(
            `SELECT so.*, s.sponsor_name, s.sponsor_type, s.website_url
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsorship_id = s.id
             WHERE so.id = ? AND so.alumni_id = ?`,
            [offerId, alumniId]
        );
        return rows.length > 0 ? rows[0] : null;
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
    static async findByAlumniAndStatus(alumniId, status) {
        const [rows] = await pool.execute(
            `SELECT so.*, s.sponsor_name, s.sponsor_type, s.website_url
             FROM sponsorship_offers so
             JOIN sponsors s ON so.sponsorship_id = s.id
             WHERE so.alumni_id = ? AND so.status = ?
             ORDER BY so.created_at DESC`,
            [alumniId, status]
        );
        return rows;
    }

    /**
     * Count offers by status for an alumni.
     * Used for dashboard summary statistics.
     */
    static async countByAlumniAndStatus(alumniId) {
        const [rows] = await pool.execute(
            `SELECT status, COUNT(*) AS count
             FROM sponsorship_offers
             WHERE alumni_id = ?
             GROUP BY status`,
            [alumniId]
        );
        return rows;
    }
    static async getAvailableBalance(alumniId) {
        const [rows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance
             FROM sponsorship_offers
             WHERE alumni_id = ? AND status = 'accepted' AND is_paid = false`,
            [alumniId]
        );
        return parseFloat(rows[0].available_balance);
    }
    static async getBalanceDetails(alumniId) {
        const [balanceRows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS available_balance,
                    COUNT(*) AS total_accepted
             FROM sponsorship_offers
             WHERE alumni_id = ? AND status = 'accepted' AND is_paid = false`,
            [alumniId]
        );

        const [paidRows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount), 0) AS total_paid_amount,
                    COUNT(*) AS total_paid
             FROM sponsorship_offers
             WHERE alumni_id = ? AND is_paid = true`,
            [alumniId]
        );

        return {
            available_balance: parseFloat(balanceRows[0].available_balance),
            total_accepted: balanceRows[0].total_accepted,
            total_paid: paidRows[0].total_paid,
            total_paid_amount: parseFloat(paidRows[0].total_paid_amount),
        };
    }
    static async updateStatus(offerId, status) {
        const [result] = await pool.execute(
            `UPDATE sponsorship_offers SET status = ?, updated_at = NOW() WHERE id = ?`,
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
    static async markAllAsPaidForAlumni(alumniId, connection = null) {
        const db = connection || pool;

        const [result] = await db.execute(
            `UPDATE sponsorship_offers
             SET is_paid = true, status = 'paid', updated_at = NOW()
             WHERE alumni_id = ? AND status = 'accepted' AND is_paid = false`,
            [alumniId]
        );

        return result.affectedRows;
    }

    static async getCredentialName(sponsorableType, sponsorableId) {
        let query = '';

        switch (sponsorableType) {
            case 'certificate':
                query = 'SELECT certificate_name AS name FROM certificates WHERE id = ?';
                break;
            case 'license':
                query = 'SELECT license_name AS name FROM licenses WHERE id = ?';
                break;
            case 'professional_course':
                query = 'SELECT course_name AS name FROM professional_courses WHERE id = ?';
                break;
            default:
                return null;
        }

        const [rows] = await pool.execute(query, [sponsorableId]);
        return rows.length > 0 ? rows[0].name : null;
    }

    static async findBySponsor(sponsorshipId) {
        const [rows] = await pool.execute(
            `SELECT so.*, ap.bio, ap.linkedin_url, u.email
             FROM sponsorship_offers so
             JOIN alumni_profiles ap ON so.alumni_id = ap.id
             JOIN users u ON ap.user_id = u.id
             WHERE so.sponsorship_id = ?
             ORDER BY so.created_at DESC`,
            [sponsorshipId]
        );
        return rows;
    }

    static async existsBySponsorAlumniCredential(sponsorshipId, alumniId, sponsorableType, sponsorableId) {
        const [rows] = await pool.execute(
            `SELECT id FROM sponsorship_offers
             WHERE sponsorship_id = ? AND alumni_id = ? AND sponsorable_type = ? AND sponsorable_id = ?
             AND status != 'declined'`,
            [sponsorshipId, alumniId, sponsorableType, sponsorableId]
        );
        return rows.length > 0;
    }
}

export default SponsorshipOfferModel;