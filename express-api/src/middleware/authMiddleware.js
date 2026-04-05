import crypto from "crypto";
import {pool} from "../config/database.js";
import {logger} from "../config/logger.js";
import {sendError} from "../utils/responseHelper.js";
import TokenGeneration from "../utils/tokenGeneration.js";

export const authenticateBearer = async (req,res,next) => {
  try{
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')){
      logger.warn('Bearer auth: Missing or malformed Authorization header',{
        ip:req.ip,
        url:req.originalUrl,
      });
      return sendError(
          res,
          'AUTHENTICATION_REQUIRED',
          'Missing or Invalid Authorization header. Expected format: Bearer <api_key>',
          401
      );
    }
    const rawToken = authHeader.substring(7).trim();
    if (!rawToken){
      return sendError(
          res,
          'AUTHENTICATION_REQUIRED',
          'API key is empty',
          401
      );
    }
    if (!TokenGeneration.isValidAPIKeyFormat(rawToken)){
      logger.warn('Bearer auth:Invalid API key format',{
        ip:req.ip,
        url:req.originalUrl,
      });
      return sendError(res,'INVALID_API_KEY','The provided API key format is invalid',401);
    }

    const tokenHash = TokenGeneration.hashToken(rawToken);

    const[rows] = await pool.execute(
        `SELECT ak.id, ak.user_id, ak.is_active, ak.revoked_at,
                    u.email, u.role
             FROM api_keys ak
             JOIN users u ON ak.user_id = u.id
             WHERE ak.key_hash = ?`,
        [tokenHash]
    );
    if (rows.length === 0){
      logger.warn('Bearer auth: API key not found in database',{
        ip:req.ip,
        url:req.originalUrl
      });
      return sendError(
          res,
          'INVALID_API_KEY',
          'The provided API key is invalid',
          401
      );
    }
    const apiKey = rows[0];

    if (!apiKey.is_active || apiKey.revoked_at != null){
      logger.warn('Bearer auth: Revoked API key attempted',{
        ip:req.ip,
        url:req.originalUrl,
        keyId:apiKey.id,
      });
      return sendError(
          res,
          'API_KEY_REVOKED',
          'This API key has been revoked',
          401
      );
    }

    req.apiKey = {
      id: apiKey.id,
      userId: apiKey.user_id,
      email: apiKey.email,
      role: apiKey.role,
    };

    req.authType = 'bearer';
    next();
  }catch (error){
    logger.error('Bearer auth: Unexpected error', {
      message: error.message,
      stack: error.stack,
      ip: req.ip,
      url: req.originalUrl,
    });

    return sendError(
        res,
        'AUTH_ERROR',
        'Authentication failed due to an internal error.',
        500
    );
  }
}

export const authenticateInternal = async (req,res,next) => {
  try{
    const internalSecret = req.headers['x-internal-secret'];
    if (!internalSecret){
      logger.warn('Internal auth:Missing X-Internal-Secret header',{
        ip:req.ip,
        url:req.originalUrl
      });
      return sendError(
          res,
          'AUTHENTICATION_REQUIRED',
          'Internal authentication required',
          401
      );
    }

    const expectedSecret = process.env.INTERNAL_API_SECRET;
    if (!expectedSecret){
      logger.warn('Internal auth: INTERNAL_API_SECRET not configured in environment variables');
      return sendError(
          res,
          'SERVER_CONFIG_ERROR',
          'Server configuration error.Contact the administrator',
          500
      );
    }

    const isValid = TokenGeneration.timingSafeCompare(internalSecret,expectedSecret);
    if (!isValid){
      logger.warn('Internal Auth: Invalid internal secret provided',{
        ip:req.ip,
        url:req.originalUrl,
      });
      return sendError(
          res,
          'INVALID_CREDENTIALS',
          'Invalid internal credentials',
          401
      );
    }
    const userId = req.body?.user_id || req.query?.user_id;
    if (!userId) {
      return sendError(res, 'USER_ID_REQUIRED', 'user_id is required', 400);
    }
    req.user = { id: userId };
    req.internalUserId = userId;
    req.authType = 'internal';
    next();
  }catch (error){
    logger.error('Internal auth:Unexpected error',{
      message:error.message,
      stack:error.stack,
      ip: req.ip,
      url: req.originalUrl,
    });
    return sendError(
        res,
        'AUTH_ERROR',
        'Authentication failed due to an internal error.',
        500
    );
  }
}
export const authenticateAny = async (req, res, next) => {
  if (req.headers.authorization && req.headers.authorization.startsWith('Bearer ')) {
    return authenticateBearer(req, res, next);
  }
  if (req.headers['x-internal-secret']) {
    return authenticateInternal(req, res, next);
  }
  logger.warn('Auth: No authentication credentials provided', {
    ip: req.ip,
    url: req.originalUrl,
    method: req.method,
  });

  return sendError(
      res,
      'AUTHENTICATION_REQUIRED',
      'Authentication required. Provide either a Bearer token in the Authorization header or an internal secret in the X-Internal-Secret header.',
      401
  );
};