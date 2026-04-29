import { pool } from "../config/database.js";

class ApiKeyPermissionModel {
    static async assignPermissions(apiKeyId, permissions) {
        for (const permission of permissions) {
            await pool.execute(
                `INSERT IGNORE INTO api_key_permissions (api_key_id, permission)
                 VALUES (?, ?)`,
                [apiKeyId, permission]
            );
        }
    }

    static async assignScope(apiKeyId, clientType) {
        await pool.execute(
            `INSERT INTO api_key_scopes (api_key_id, client_type)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE client_type = VALUES(client_type), updated_at = NOW()`,
            [apiKeyId, clientType]
        );
    }

    static async hasPermission(apiKeyId, permission) {
        const [rows] = await pool.execute(
            `SELECT id FROM api_key_permissions
             WHERE api_key_id = ? AND permission = ?`,
            [apiKeyId, permission]
        );
        return rows.length > 0;
    }

    static async getPermissions(apiKeyId) {
        const [rows] = await pool.execute(
            `SELECT permission FROM api_key_permissions WHERE api_key_id = ?`,
            [apiKeyId]
        );
        return rows.map(r => r.permission);
    }

    static async getScope(apiKeyId) {
        const [rows] = await pool.execute(
            `SELECT client_type FROM api_key_scopes WHERE api_key_id = ?`,
            [apiKeyId]
        );
        return rows.length > 0 ? rows[0].client_type : null;
    }

    static getPermissionsForClientType(clientType) {
        const permissionMap = {
            analytics_dashboard: [
                'read:alumni',
                'read:analytics',
                'read:export',
            ],
            ar_app: [
                'read:alumni_of_day',
            ],
            third_party: [
                'read:alumni_of_day',
            ],
        };
 
        return permissionMap[clientType] || [];
    }
}

export default ApiKeyPermissionModel;
