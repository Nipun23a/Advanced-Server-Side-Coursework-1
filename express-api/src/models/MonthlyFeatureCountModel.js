const pool = require('../config/database');

class MonthlyFeatureCountModel {
    /**
     * Get the monthly feature count for a user in a specific  month 
     * 
     * @param {number} userId 
     * @param {number} year 
     * @param {number} month 1-12
     * @returns {object|null} {count,attendent_event} or null if not record
     */
    static async findByUserAndMonth(userId, year, month) {
        const [rows] = await pool.execute(
            'SELECT count,attendent_event FROM monthly_feature_count WHERE user_id = ? AND year = ? AND month = ?',
            [userId, year, month]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Get the current month feature count for a user
     * This is convience method that uses automatically uses the current year and month
     * 
     * @param {number} userId 
     * @returns {object} {count,attended_event,year,month}
     */
    
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

    /**
     * Increament the feature count for a user for a specific month.
     * Uses on DUPLICATE KEY UPDATE for atomic upsert - if record exists, it creates one with count = 1 if it does 
     * exist, it increments the count by 1
     * 
     * Called by the winner selection algorithm within a transaction.
     * 
     * @param {number} userId 
     * @param {number} year 
     * @param {number} month 
     * @param {object|null} connection 
     * @returns {boolean} 
     */


    static async increamentCount(userId, year, month, connection = null) {
        const db = connection || pool;
        const [result] = await db.execute(
            `INSERT INTO monthly_feature_count (user_id, year, month, count,attendent_event)
             VALUES (?, ?, ?, 1,0)
             ON DUPLICATE KEY UPDATE count = count + 1`,
            [userId, year, month]
        );
        return result.affectedRows > 0;
    }

    /**
     * Mark that user attended a university event this month.
     * 
     * This grant them the ability to be featured a 4th time
     * 
     * @param {number} userId 
     * @param {number} year 
     * @param {number} month 
     * @param {object|null} connection 
     * @returns {boolean} 
     */


    static async markEventAttended(userId, year, month, connection = null){
        const [result] = await (connection || pool).execute(
            'INSERT INTO monthly_feature_count (user_id, year, month, count,attendent_event) VALUES (?, ?, ?, 0,true) ON DUPLICATE KEY UPDATE attendent_event = true',
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
module.exports = MonthlyFeatureCountModel;