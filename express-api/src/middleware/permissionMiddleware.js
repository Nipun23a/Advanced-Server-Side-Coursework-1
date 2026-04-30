import ApiKeyPermissionModel from '../models/ApiKeyPermissionModel.js';
import {sendError} from '../utils/responseHelper.js';
import {logger} from '../config/logger.js';


const requirePermission = (requiredPermission) => {
    return async (req, res, next) => {
        try {
            if (!req.apiKey){
                return sendError(res, "AUTHENTICATION_REQUIRED","Authentication required", 401);
            }
            if (req.authType === "internal"){
                return next();
            }
            const apiKeyId = req.apiKey.id;
            const hasPermission = await ApiKeyPermissionModel.hasPermission(apiKeyId, requiredPermission);
            if (!hasPermission) {
                // Get what permissions this key actually has (for logging)
                const actualPermissions = await ApiKeyPermissionModel.getPermissions(apiKeyId);
 
                logger.warn('Permission denied', {
                    apiKeyId,
                    required: requiredPermission,
                    actual: actualPermissions,
                    url: req.originalUrl,
                    ip: req.ip,
                });
 
                return sendError(
                    res,
                    'INSUFFICIENT_PERMISSIONS',
                    `This API key does not have the required permission: ${requiredPermission}. ` +
                    `Your key has: [${actualPermissions.join(', ')}]. ` +
                    `Request a key with the correct client type from the developer dashboard.`,
                    403
                );
            }

            next();
        } catch (error) {
            logger.error('Permission middleware error:', error);
            return sendError(res, 'PERMISSION_CHECK_ERROR', 'Failed to verify permissions.', 500);

        }
    }

}

export {requirePermission};
