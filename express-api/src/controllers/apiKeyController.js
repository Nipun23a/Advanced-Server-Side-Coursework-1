import ApiKeyService from "../services/ApiKeyService.js";
import {sendError, sendSuccess} from "../utils/responseHelper.js";
import {logger} from "../config/logger.js";

class ApiKeyController{
    static async generateKey(req, res) {
        try {
            const userId = req.user?.id || req.internalUserId;
            console.log(userId);
            if (!userId) {
                return sendError(
                    res,
                    'MISSING_USER_ID',
                    'User ID is required to generate an API key.',
                    400
                );
            }

            const result = await ApiKeyService.generateKey(userId);

            return sendSuccess(
                res,
                result,
                'API key generated successfully. Save this key - it cannot be retrieved.',
                201
            );

        } catch (error) {
            logger.error('ApiKeyController.generateKey error:', {
                message: error.message,
                stack: error.stack,
                ip: req.ip,
            });

            return sendError(
                res,
                'KEY_GENERATION_ERROR',
                'Failed to generate API key.',
                500
            );
        }
    }

    static async listKeys(req,res) {
        try {
            const userId = req.user?.id || req.internalUserId;
            console.log(userId);
            if (!userId) {
                return sendError(
                    res,
                    'MISSING_USER_ID',
                    'User ID is required to list API keys.',
                     400
                );
            }
            const result = await ApiKeyService.listKeys(userId);
            return sendSuccess(res, result, 'API keys listed successfully.');
        }catch (error){
            logger.error('ApiKeyController.listKeys error:', {
                message: error.message,
                stack: error.stack,
                ip: req.ip,
            });
            return sendError(
                res,
                'KEY_LISTING_ERROR',
                'Failed to list API keys.',
                 500
            );
        }
    }

    static async getKeyStats(req,res){
        try{
            const keyId = parseInt(req.params.id);
            const result = await ApiKeyService.getKeyStats(keyId);
            if (!result){
                return sendError(
                    res,
                    'KEY_NOT_FOUND',
                    'API key not found.',
                     404
                );
            }
            return sendSuccess(res,result,'API key stats retrieved successfully.');
        }catch (error){
            logger.error('ApiKeyController.getKeyStats error:', {
                message: error.message,
                stack: error.stack,
                keyId: req.params.id,
            });
            return sendError(
                res,
                'KEY_STATS_ERROR',
                'Failed to retrieve API key stats.',
                  500
            );
        }
    }

    static async revokeKey(req,res){
        try{
            const keyId = parseInt(req.params.id);
            const userId = req.body.user_id || req.internalUserId;
            if (!userId) {
                return sendError(
                    res,
                    'MISSING_USER_ID',
                    'User ID is required for ownership verification.',
                    400
                );
            }
            const result = await ApiKeyService.revokeKey(keyId, userId);
            return sendSuccess(res, result, 'API key revoked successfully.');
        }catch (error){
            logger.error('ApiKeyController.revokeKey error:', {
                message: error.message,
                stack: error.stack,
                keyId: req.params.id,
            });

            if(error.code === 'KEY_NOT_FOUND'){
                return sendError(res,error.code,error.message,404);
            }
            if (error.code === 'KEY_ALREADY_REVOKED'){
                return sendError(res,error.code,error.message,400);
            }
            return sendError(
                res,
                'KEY_REVOCATION_ERROR',
                'Failed to revoke API key.',
                 500
            );
        }
    }
}

export default ApiKeyController;