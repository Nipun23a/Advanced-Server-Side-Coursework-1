import { pool } from '../config/database.js';

class SponsorshipOfferModel {
    static async findAlumniProfileById(alumniId) {
        const [rows] = await pool.execute(
            'SELECT id FROM alumni_profiles WHERE id = ? LIMIT 1',
            [alumniId]
        );

        return rows.length > 0 ? rows[0] : null;
    }

    static async findAlumniProfileIdByUserId(userId) {
        const [rows] = await pool.execute(
            'SELECT id FROM alumni_profiles WHERE user_id = ? LIMIT 1',
            [userId]
        );

        return rows.length > 0 ? rows[0].id : null;
    }

    static async create(data) {
        const {
            sponsorship_id,
            alumni_id,
            sponsor_id,
            user_id,
            sponsorable_id,
            sponsorable_type,
            offer_amount,
        } = data;

        const normalizedSponsorshipId = sponsorship_id || sponsor_id;
        let normalizedAlumniId = alumni_id;

        if (!normalizedAlumniId && user_id) {
            normalizedAlumniId = await this.findAlumniProfileIdByUserId(user_id);
        }


        if (!normalizedSponsorshipId || !normalizedAlumniId || !offer_amount) {
            throw new Error('Missing required sponsorship fields');
        }

        const [result] = await pool.execute(
            `INSERT INTO sponsorship_offers
             (sponsorship_id, alumni_id, sponsorable_id, sponsorable_type, offer_amount, remaining_amount, status, is_paid, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', false, NOW(), NOW())`,
            [normalizedSponsorshipId, normalizedAlumniId, sponsorable_id, sponsorable_type, offer_amount, offer_amount]
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

    static async findAcceptedAvailableByIdAndUser(offerId, userId) {
        const [rows] = await pool.execute(
            `SELECT so.*
             FROM sponsorship_offers so
             JOIN alumni_profiles ap ON ap.id = so.alumni_id
             WHERE so.id = ?
               AND ap.user_id = ?
               AND so.status = 'accepted'
               AND so.remaining_amount > 0
             LIMIT 1`,
            [offerId, userId]
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
            `SELECT COALESCE(SUM(remaining_amount), 0) AS available_balance
             FROM sponsorship_offers
             WHERE alumni_id = ? AND status = 'accepted' AND remaining_amount > 0`,
            [alumniId]
        );
        return parseFloat(rows[0].available_balance);
    }
    static async getBalanceDetails(alumniId) {
        const [balanceRows] = await pool.execute(
            `SELECT COALESCE(SUM(remaining_amount), 0) AS available_balance,
                    COUNT(*) AS total_accepted
             FROM sponsorship_offers
             WHERE alumni_id = ? AND status = 'accepted' AND remaining_amount > 0`,
            [alumniId]
        );

        const [paidRows] = await pool.execute(
            `SELECT COALESCE(SUM(offer_amount - remaining_amount), 0) AS total_paid_amount,
                    COUNT(*) AS total_paid
             FROM sponsorship_offers
             WHERE alumni_id = ? AND (offer_amount - remaining_amount) > 0`,
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
             SET remaining_amount = 0, is_paid = true, status = 'paid', updated_at = NOW()
             WHERE id = ?`,
            [offerId]
        );
        return result.affectedRows > 0;
    }
    static async consumeBalanceForWinningBid(alumniId, bidAmount, connection = null) {
        const db = connection || pool;
        let remainingBidAmount = parseFloat(bidAmount);
        let affectedOffers = 0;

        const [offers] = await db.execute(
            `SELECT id, offer_amount, remaining_amount
             FROM sponsorship_offers
             WHERE alumni_id = ? AND status = 'accepted' AND remaining_amount > 0
             ORDER BY created_at ASC, id ASC`,
            [alumniId]
        );

        for (const offer of offers) {
            if (remainingBidAmount <= 0) {
                break;
            }

            const currentRemaining = parseFloat(offer.remaining_amount);
            const deduction = Math.min(currentRemaining, remainingBidAmount);
            const nextRemaining = currentRemaining - deduction;
            const isPaid = nextRemaining <= 0;

            await db.execute(
                `UPDATE sponsorship_offers
                 SET remaining_amount = ?, is_paid = ?, status = ?, updated_at = NOW()
                 WHERE id = ?`,
                [nextRemaining, isPaid, isPaid ? 'paid' : 'accepted', offer.id]
            );

            remainingBidAmount -= deduction;
            affectedOffers += 1;
        }

        if (remainingBidAmount > 0) {
            throw new Error(`Insufficient accepted sponsorship funds to cover winning bid. Uncovered amount: ${remainingBidAmount.toFixed(2)}`);
        }

        return affectedOffers;
    }

    static normalizeSponsorableType(sponsorableType) {
        if (!sponsorableType) {
            return null;
        }

        if (sponsorableType === 'licence') {
            return 'license';
        }

        return sponsorableType;
    }


    static async getCredentialName(sponsorableType, sponsorableId) {
        const normalizedType = this.normalizeSponsorableType(sponsorableType);
        let query = '';

        switch (normalizedType) {
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
