import {pool} from "../config/database.js";
import {logger} from "../config/logger.js";
import BidModel from "../models/BidModel.js";
import FeatureAlumniModel from "../models/FeatureAlumniModel.js";
import MonthlyFeatureCountModel from "../models/MonthlyFeatureCountModel.js";
import SponsorshipOfferModel from "../models/SponsorshipOfferModel.js";

class WinnerService {
    static async selectDailyWinner(){
        const connection = await pool.getConnection();
        try {

            await connection.beginTransaction();

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0]; // YYYY-MM-DD
 

            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth() + 1;

            const normalLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT) || 3;
            const eventLimit = parseInt(process.env.MONTHLY_FEATURE_LIMIT_WITH_EVENT) || 4;

            const [candidates] = await connection.execute(
                `SELECT b.id, b.user_id, b.bid_amount, b.created_at
                 FROM bids b
                 WHERE b.bid_date = ? AND b.bid_status = 'active' AND b.is_cancelled = false
                 ORDER BY b.bid_amount DESC, b.created_at ASC`,
                [tomorrowStr]
            );
            if(candidates.length === 0){
                logger.info(`Winner selection: No bids found for ${tomorrowStr}. No winner selected.`);
                await connection.commit();
                connection.release();
                return null;
            }
            logger.info(`Winner selection: ${candidates.length} candidate(s) found for ${tomorrowStr}`);
            let winner = null;
            for (const candidate of candidates){
                const [monthlyRows] = await connection.execute(
                    `SELECT count, attended_event FROM monthly_feature_count
                     WHERE user_id = ? AND year = ? AND month = ?`,
                    [candidate.user_id, year, month]
                );
                const currentCount = monthlyRows.length > 0 ? monthlyRows[0].count : 0;
                const attendedEvent = monthlyRows.length > 0
                    ? Boolean(monthlyRows[0].attended_event)
                    : false;
                const maxAllowed = attendedEvent ? eventLimit : normalLimit;

                if (currentCount < maxAllowed){
                    winner = candidate;
                    logger.info(
                        `Winner selection: User ${candidate.user_id} eligible ` +
                        `(featured ${currentCount}/${maxAllowed} times this month)`
                    );
                    break;
                }

                logger.info(
                    `Winner selection: User ${candidate.user_id} reached monthly limit ` +
                    `(${currentCount}/${maxAllowed}). Skipping to next candidate.`
                );
            }
            if (!winner){
                logger.info(
                    `Winner selection: All ${candidates.length} bidder(s) for ${tomorrowStr} ` +
                    `have reached their monthly feature limits. No winner selected.`
                );
                await connection.commit();
                connection.release();
                return null;
            }

            await connection.execute(
                "UPDATE bids SET bid_status = 'won', updated_at = NOW() WHERE id = ?",
                [winner.id]
            );

            const [lostResult] = await connection.execute(
                `UPDATE bids SET bid_status = 'lost', updated_at = NOW()
                 WHERE bid_date = ? AND id != ? AND bid_status = 'active' AND is_cancelled = false`,
                [tomorrowStr, winner.id]
            );
 
            logger.info(`Winner selection: ${lostResult.affectedRows} bid(s) marked as lost`);
            await connection.execute(
                `INSERT INTO featured_alumni (user_id, bid_id, featured_date, created_at)
                 VALUES (?, ?, ?, NOW())`,
                [winner.user_id, winner.id, tomorrowStr]
            );
            await connection.execute(
                `INSERT INTO monthly_feature_count (user_id, year, month, count, attended_event)
                 VALUES (?, ?, ?, 1, false)
                 ON DUPLICATE KEY UPDATE count = count + 1`,
                [winner.user_id, year, month]
            );

            const [paidResult] = await connection.execute(
                `UPDATE sponsorship_offers
                 SET is_paid = true, status = 'paid', updated_at = NOW()
                 WHERE user_id = ? AND status = 'accepted' AND is_paid = false`,
                [winner.user_id]
            );
            logger.info(`Winner selection: ${paidResult.affectedRows} sponsorship offer(s) marked as paid`);

                        await connection.commit();
 
            logger.info(
                `Winner selection: COMPLETE for ${tomorrowStr} — ` +
                `user_id=${winner.user_id}, bid_id=${winner.id}, amount=${winner.bid_amount}`
            );
            return {
                winner_user_id: winner.user_id,
                bid_id: winner.id,
                bid_amount: parseFloat(winner.bid_amount),
                featured_date: tomorrowStr,
                losers_count: lostResult.affectedRows,
                sponsorships_paid: paidResult.affectedRows,
            };
        } catch (error) {
            await connection.rollback();
            logger.error('Winner selection: FAILED — transaction rolled back:', error);
            throw error;
        }finally{
            connection.release();
        }
    }

    static async getTodayFeatureProfile(){
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
        if (rows.length === 0){
            logger.info(`No featured alumni found for today (${today})`);
            return null;
        }
        const alumni = rows[0];
        const [degrees, certificates, licences, courses, employment] = await Promise.all([
            pool.execute(
                `SELECT id, degree_name, institution_url, completion_date
                 FROM degrees
                 WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)`,
                [alumni.user_id]
            ),
            pool.execute(
                `SELECT id, certificate_name, provider_url, completion_date
                 FROM certificates
                 WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)`,
                [alumni.user_id]
            ),
            pool.execute(
                `SELECT id, licence_name, provider_url, completion_date, expiry_date
                 FROM licences
                 WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)`,
                [alumni.user_id]
            ),
            pool.execute(
                `SELECT id, course_name, provider_url, completion_date, end_date
                 FROM professional_courses
                 WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)`,
                [alumni.user_id]
            ),
            pool.execute(
                `SELECT id, company_name, role, start_date, end_date
                 FROM employment_history
                 WHERE profile_id = (SELECT id FROM alumni_profiles WHERE user_id = ?)`,
                [alumni.user_id]
            ),
        ]);
        return {
            featured_date: alumni.featured_date,
            email: alumni.email,
            bio: alumni.bio,
            linkedin_url: alumni.linkedin_url,
            profile_image_url: alumni.profile_image_url,
            degrees: degrees[0],
            certificates: certificates[0],
            licences: licences[0],
            professional_courses: courses[0],
            employment_history: employment[0],
        }
    }

    static async getTodayWinner(){
        const today = new Date().toISOString().split('T')[0];
        const [rows] = await pool.execute(
            `SELECT fa.id, fa.featured_date, fa.user_id, fa.bid_id,
                    b.bid_amount, b.bid_date, b.created_at AS bid_created_at,
                    u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             WHERE fa.featured_date = ?`,
            [today]
        );

        return rows.length > 0 ? rows[0] : null;
    }

    static async getFeaturedHistory(page = 1, limit = 10){
        const offset = (page - 1) * limit;
        const [rows] = await pool.execute(
            `SELECT fa.featured_date, u.email,
                    ap.bio, ap.linkedin_url, ap.profile_image_url
             FROM featured_alumni fa
             JOIN users u ON fa.user_id = u.id
             LEFT JOIN alumni_profiles ap ON u.id = ap.user_id
             ORDER BY fa.featured_date DESC
             LIMIT ? OFFSET ?`,
            [limit, offset]
        );
        const [countResult] = await pool.execute(
            'SELECT COUNT(*) AS total FROM featured_alumni'
        );
 
        const total = countResult[0].total;
        return {
            featured: rows,
            pagination: {
                page,
                limit,
                total,
                totalPages: Math.ceil(total / limit),
            }
        };
    }

    static async getWinnerHistory(page = 1, limit = 10){
        const offset = (page - 1) * limit;
        const [rows] = await pool.execute(
            `SELECT fa.featured_date, fa.user_id, fa.bid_id,
                    b.bid_amount,
                    u.email
             FROM featured_alumni fa
             JOIN bids b ON fa.bid_id = b.id
             JOIN users u ON fa.user_id = u.id
             ORDER BY fa.featured_date DESC
             LIMIT ? OFFSET ?`,
            [limit, offset]
        );

        const [countResult] = await pool.execute(
            'SELECT COUNT(*) AS total FROM featured_alumni'
        );

        const total = countResult[0].total;
        return {
            winners: rows,
            pagination: {
                page,
                limit,
                total,
                total_pages: Math.ceil(total / limit),
            },
        };
    }

    static async hasWinnerForDate(date) {
        const [rows] = await pool.execute(
            'SELECT id FROM featured_alumni WHERE featured_date = ?',
            [date]
        );
        return rows.length > 0;
    }

    static async triggerManualSelection(){
        const tommorrow = new Date();
        tommorrow.setDate(tommorrow.getDate() + 1);
        const dateStr = tommorrow.toISOString().split('T')[0];

        const alreadySelected = await this.hasWinnerForDate(dateStr);
        if (alreadySelected){
            logger.warn(`Manual selection: Winner already exists for ${tomorrowStr}`);
            return {
                result: null,
                message: `A winner has already been selected for ${tomorrowStr}.`,
            };
        }

        const result = await this.selectDailyWinner();
        return{
            result,
            message: result
                ? `Winner selected for ${tomorrowStr}: user_id=${result.winner_user_id}`
                : `No eligible bids found for ${tomorrowStr}.`,
        };
    }
}

module.exports = WinnerService;