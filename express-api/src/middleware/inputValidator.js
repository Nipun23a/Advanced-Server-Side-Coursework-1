import {body, param, validationResult,query} from "express-validator";
import {sendError} from "../utils/responseHelper.js";

export const handleValidationErrors = (req,res,next) =>{
  const errors = validationResult(req);
  if (!errors.isEmpty()){
    const formattedErrors = errors.array().map(err => ({
      field: err.path,
      message: err.msg,
      value: err.value,
    }));
    return sendError(
        res,
        'VALIDATION_ERROR',
        'Input validation failed.',
        400,
        formattedErrors
    );
  }
  next();
}

export const validatePlaceBid = [
  body('bid_amount')
      .exists().withMessage('Bid amount is required.')
      .isFloat({ min: 0.01 }).withMessage('Bid amount must be a positive number greater than zero.')
      .custom((value) => {
        const decimalPart = value.toString().split('.')[1];
        if (decimalPart && decimalPart.length > 2) {
          throw new Error('Bid amount cannot have more than 2 decimal places.');
        }
        return true;
      }),

  body('bid_date')
      .exists().withMessage('Bid date is required.')
      .isDate({ format: 'YYYY-MM-DD' }).withMessage('Bid date must be in YYYY-MM-DD format.')
      .custom((value) => {
        const bidDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        bidDate.setHours(0, 0, 0, 0);

        if (bidDate <= today) {
          throw new Error('Bid date must be tomorrow or a future date.');
        }
        return true;
      }),

  body('user_id')
      .optional()
      .isInt({ min: 1 }).withMessage('User ID must be a positive integer.'),

  handleValidationErrors,
];

export const validateUpdateBid = [
  param('id')
      .isInt({ min: 1 }).withMessage('Bid ID must be a positive integer.'),

  body('bid_amount')
      .exists().withMessage('New bid amount is required.')
      .isFloat({ min: 0.01 }).withMessage('Bid amount must be a positive number greater than zero.')
      .custom((value) => {
        const decimalPart = value.toString().split('.')[1];
        if (decimalPart && decimalPart.length > 2) {
          throw new Error('Bid amount cannot have more than 2 decimal places.');
        }
        return true;
      }),

  handleValidationErrors,
];

export const validateCancelBid = [
  param('id')
      .isInt({ min: 1 }).withMessage('Bid ID must be a positive integer.'),

  handleValidationErrors,
];

export const validateGenerateKey = [
  body('user_id')
      .optional()
      .isInt({ min: 1 }).withMessage('User ID must be a positive integer.'),

  handleValidationErrors,
];

export const validateRevokeKey = [
  param('id')
      .isInt({ min: 1 }).withMessage('API Key ID must be a positive integer.'),

  handleValidationErrors,
];

export const validateSponsorshipResponse = [
  param('id')
      .isInt({ min: 1 }).withMessage('Offer ID must be a positive integer.'),

  body('action')
      .exists().withMessage('Action is required.')
      .isIn(['accept', 'decline']).withMessage('Action must be either "accept" or "decline".'),

  handleValidationErrors,
];

export const validatePagination = [
  query('page')
      .optional()
      .isInt({ min: 1 }).withMessage('Page must be a positive integer.'),

  query('limit')
      .optional()
      .isInt({ min: 1, max: 100 }).withMessage('Limit must be between 1 and 100.'),

  handleValidationErrors,
];

export const validateIdParam = [
  param('id')
      .isInt({ min: 1 }).withMessage('ID must be a positive integer.'),

  handleValidationErrors,
];

