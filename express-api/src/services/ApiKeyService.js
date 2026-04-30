
import ApiKeyModel from "../models/ApiKeyModel.js";
import ApiKeyPermissionModel from "../models/ApiKeyPermissionModel.js";
import ApiUsageLogModel from "../models/ApiUsageLogModel.js";
import TokenGeneration from "../utils/tokenGeneration.js";
import {logger} from "../config/logger.js";


class ApiKeyService {
    static async generateKeyWithScope(userId, clientType) {
        const validTypes = ['analytics_dashboard', 'ar_app', 'third_party'];
        if (!validTypes.includes(clientType)) {
            const error = new Error(`Invalid client type. Must be one of: ${validTypes.join(', ')}`);
            error.code = 'INVALID_CLIENT_TYPE';
            error.status = 400;
            throw error;
        }

        const rawKey = TokenGeneration.generateAPIKey();
        const keyHash = TokenGeneration.hashToken(rawKey);
        const result = await ApiKeyModel.create(userId, keyHash);
        const apiKeyId = result.insertId;

        const permissions = ApiKeyPermissionModel.getPermissionsForClientType(clientType);
        await ApiKeyPermissionModel.assignPermissions(apiKeyId, permissions);
        await ApiKeyPermissionModel.assignScope(apiKeyId, clientType);

        logger.info(
            `API key generated with scope: key_id=${apiKeyId}, user_id=${userId}, ` +
            `client_type=${clientType}, permissions=[${permissions.join(', ')}]`
        );

        return {
            key_id: apiKeyId,
            key: rawKey,
            client_type: clientType,
            permissions,
            warning: 'Save this key now. You will not be able to see it again!',
        };
    }

    static async generateKey(userId){
        const rawKey = TokenGeneration.generateAPIKey();
        const keyHash = TokenGeneration.hashToken(rawKey);
        const result = await ApiKeyModel.create(userId,keyHash);
        logger.info(`API key generated: key_id=${result.insertId}, user_id=${userId}`);
        return {
            key_id: result.insertId,
            key: rawKey,
            warning: 'Save this key now. You will not be able to see it again!',
        };
    }

    static async validateKey(rawKey){
        if (!TokenGeneration.isValidAPIKeyFormat(rawKey)){
            logger.warn('API key formal validation failed');
            return null;
        }

        const keyHash = TokenGeneration.hashToken(rawKey);
        const apiKey = await ApiKeyModel.findByHash(keyHash);
        if (!apiKey){
            logger.warn('API key not found in database');
            return null;
        }
        if (!apiKey.is_active || apiKey.revoked_at != null){
            logger.warn(`Revoked API key attempted: key_id=${apiKey.id}`);
            return null;
        }
        return apiKey;
    }

    static async listKeys(userId) {
        const keys = await ApiKeyModel.findAllByUser(userId);
        return { keys };
    }

    static async getKeyStats(keyId){
        const key = await ApiKeyModel.findById(keyId);
        if (!key){
            return null;
        }
        const statistics = await ApiUsageLogModel.getFullStats(keyId);

        return {
            key: {
                id: key.id,
                is_active: key.is_active,
                created_at: key.created_at,
                revoked_at: key.revoked_at,
            },
            statistics,
        };
    }

    static async revokeKey(keyId,userId){
        const key = await ApiKeyModel.findByIdAndUser(keyId,userId);
        if (!key){
            const error = new Error('API key not found');
            error.code = 'NOT_FOUND';
            error.status = 404;
            throw error;
        }
        if (!key.is_active){
            const error = new Error('This API key has already been revoked.');
            error.code = 'KEY_ALREADY_REVOKED';
            error.status = 400;
            throw error;
        }
        await ApiKeyModel.revoke(keyId);
        logger.info(`API key revoked: key_id=${keyId}, user_id=${userId}`);
        return {
            id: keyId,
            is_active: false,
            revoked_at: new Date().toISOString(),
        };
    }
}

export default ApiKeyService;