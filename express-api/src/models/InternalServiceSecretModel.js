import {pool} from "../config/database.js";

class InternalServiceSecretModel {
    static serviceName = 'express_api';

    static async findActiveByHash(secretHash, serviceName = InternalServiceSecretModel.serviceName) {
        const [rows] = await pool.execute(
            `SELECT id, service_name, secret_hash, created_at, updated_at
             FROM internal_service_secrets
             WHERE service_name = ?
               AND secret_hash = ?
               AND is_active = 1
             ORDER BY id DESC
             LIMIT 1`,
            [serviceName, secretHash]
        );

        return rows[0] || null;
    }

    static async hasAnyActiveSecret(serviceName = InternalServiceSecretModel.serviceName) {
        const [rows] = await pool.execute(
            `SELECT COUNT(*) AS total
             FROM internal_service_secrets
             WHERE service_name = ?
               AND is_active = 1`,
            [serviceName]
        );

        return Number(rows[0]?.total || 0) > 0;
    }
}

export default InternalServiceSecretModel;
