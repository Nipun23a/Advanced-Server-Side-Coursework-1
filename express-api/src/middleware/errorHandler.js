import {logger} from "../config/logger.js";

export const errorHandler = (err,req,res,next) => {
  logger.error('Unhandled error caught by global handler:',{
    message: err.message,
    stack: err.stack,
    method: req.method,
    url: req.originalUrl,
    ip: req.ip,
    authType: req.authType || 'none',
    apiKeyId: req.apiKey ? req.apiKey.id : null,
  });

  if (err.message && err.message.includes('CORS')){
    return res.status(403).json({
      success:false,
      error:{
        code:'CORS_ERROR',
        message:'Cross-origin request blocked by CORS policy',
        details:[],
      }
    });
  }
  if (err.type === 'entity.parse.failed'){
    return res.status(400).json({
      success:false,
      error: {
        code:'INVALID_JSON',
        message:"Request body contains invalid JSON. Please check your syntax",
        details:[],
      }
    });
  }

  if (err.type === 'entity.too.large') {
    return res.status(413).json({
      success: false,
      error: {
        code: 'PAYLOAD_TOO_LARGE',
        message: 'Request body exceeds the maximum allowed size of 10KB.',
        details: [],
      },
    });
  }

  if (err.code === 'ECONNREFUSED') {
    return res.status(503).json({
      success: false,
      error: {
        code: 'SERVICE_UNAVAILABLE',
        message: 'Database service is temporarily unavailable. Please try again later.',
        details: [],
      },
    });
  }

  if (err.code === 'ER_ACCESS_DENIED_ERROR') {
    return res.status(503).json({
      success: false,
      error: {
        code: 'SERVICE_UNAVAILABLE',
        message: 'The service is temporarily unavailable. Please try again later.',
        details: [],
      },
    });
  }

  if (err.code === 'ER_DUP_ENTRY') {
    return res.status(409).json({
      success: false,
      error: {
        code: 'DUPLICATE_ENTRY',
        message: 'This record already exists.',
        details: [],
      },
    });
  }
  const statusCode = err.statusCode || err.status || 500;

  const response = {
    success: false,
    error: {
      code: 'INTERNAL_ERROR',
      message: process.env.NODE_ENV === 'production'
          ? 'An unexpected error occurred. Please try again later.'
          : err.message,
      details: process.env.NODE_ENV === 'production'
          ? []
          : [{ stack: err.stack }],
    },
  };
  res.status(statusCode).json(response);
}

export const notFoundHandler = (req, res) => {
  logger.warn('Route not found', {
    method: req.method,
    url: req.originalUrl,
    ip: req.ip,
  });

  res.status(404).json({
    success: false,
    error: {
      code: 'NOT_FOUND',
      message: `Endpoint ${req.method} ${req.originalUrl} does not exist. Check the API documentation at /api-docs for available endpoints.`,
      details: [],
    },
  });
};
