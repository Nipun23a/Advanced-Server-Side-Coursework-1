/**
 * ApiKeyModel
 * 
 * Handles all database operations for the api_keys table.
 * 
 */

const pool = require('../config/database');

class ApiKeyModel {
    /**
     * Creates a new API key for the specified user.
     * 
     * @param {number} userId 
     * @param {string} keyHash 
     * @returns {object} {id, user_id, is_active, created_at}
     * 
     */

    static async create(userId, keyHash){
        const [result] = await pool.execute(
            'INSERT INTO api_keys (user_id, hashed_key,is_active, created_at) VALUES (?, ?,true, NOW())',
            [userId, keyHash]
        );
        return {
            id: result.insertId,
            user_id: userId,
            is_active: true,
            created_at: new Date().toISOString(),
        };
    }

    /**
     * Find an API key record by its hash.
     * Used during bearer token authentication to validate incoming keys.
     * Joins with users table to get role and email for the authenticated context.
     * 
     * @param {string} keyHash  The hashed API key to look up.
     * @returns {object} {id, user_id, is_active, revoked_at, email, role}
     */

    static async findByHash(keyHash){
        const [rows] = await pool.execute(
                        `SELECT ak.id, ak.user_id, ak.is_active, ak.revoked_at,
                    u.email, u.role
             FROM api_keys ak
             JOIN users u ON ak.user_id = u.id
             WHERE ak.hashed_key = ?`,
            [keyHash]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Find an API key record by its ID.
     * 
     * @param {number} keyId 
     * @returns {object} {id, user_id, is_active, created_at, revoked_at}
     * 
     */

    static async findById(keyId){
        const [rows] = await pool.execute(
            'SELECT id, user_id, is_active, created_at, revoked_at FROM api_keys WHERE id = ?',
            [keyId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Find an API key by ID and userID
     * Used to verify ownership before allowing revocation or other management actions on the key revocation
     * 
     * @param {number} keyId
     * @param {number} userId
     * @return {object|null} {id, user_id, is_active, created_at, revoked_at} or null if not found or not owned by user
     */
    
    static async findByIdAndUser(keyId, userId) {
        const [rows] = await pool.execute(
            'SELECT * FROM api_keys WHERE id = ? AND user_id = ?',
            [keyId, userId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * List all API keys for a user
     * @param {number} userId 
     * @returns {array} Array of key records
     */
    static async findAllByUser(userId) {
        const [rows] = await pool.execute(
            'SELECT id, is_active, created_at, revoked_at FROM api_keys WHERE user_id = ? ORDER BY created_at DESC',
            [userId]
        );
        return rows;
    }
    
    /**
     * Revoke an API key by setting is_active to false and recording the timestamp
     * 
     * @param {number} keyId
     * @return {boolean}
     * 
     */
    static async revoke(keyId) {
        const [result] = await pool.execute(
            'UPDATE api_keys SET is_active = false, revoked_at = NOW() WHERE id = ?',
            [keyId]
        )
        return result.affectedRows > 0;
    }
}

module.exports = ApiKeyModel;