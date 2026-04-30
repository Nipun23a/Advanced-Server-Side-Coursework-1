import { pool } from "../config/database.js";

class ApiKeyModel {

    static async create(userId, keyHash) {
        const [result] = await pool.execute(
            `INSERT INTO api_keys (user_id, key_hash, is_active, created_at)
             VALUES (?, ?, true, NOW())`,
            [userId, keyHash]
        );

        return {
            id: result.insertId,
            user_id: userId,
            is_active: true,
            created_at: new Date().toISOString(),
        };
    }

    static async findByHash(keyHash) {
        const [rows] = await pool.execute(
            `SELECT ak.id, ak.user_id, ak.is_active, ak.revoked_at,
                    u.email, u.role
             FROM api_keys ak
                      JOIN users u ON ak.user_id = u.id
             WHERE ak.key_hash = ?`,
            [keyHash]
        );
        return rows[0] || null;
    }

    static async findById(keyId){
        const [rows] = await pool.execute(
            'SELECT id,user_id,is_active,created_at,revoked_at FROM api_keys WHERE id = ?',
            [keyId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findAllByUser(userId) {
        const [rows] = await pool.execute(
            `SELECT ak.id, ak.is_active, ak.created_at, ak.revoked_at, aks.client_type
             FROM api_keys ak
             LEFT JOIN api_key_scopes aks ON aks.api_key_id = ak.id
             WHERE ak.user_id = ?
             ORDER BY ak.created_at DESC`,
            [userId]
        );
        return rows;
    }

    static async findByIdAndUser(keyId, userId) {
        const [rows] = await pool.execute(
            'SELECT id, user_id, is_active, created_at, revoked_at FROM api_keys WHERE id = ? AND user_id = ?',
            [keyId, userId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async revoke(keyId) {
        const [result] = await pool.execute(
            `UPDATE api_keys
             SET is_active = false, revoked_at = NOW()
             WHERE id = ?`,
            [keyId]
        );
        return result.affectedRows > 0;
    }
}

export default ApiKeyModel;
