const {body, validationResult} = require('express-validator');

const validateBidInput = [
    body("bid_amount")
    .isFloat({ gt: 0 })
    .withMessage("Bid must be greater than 0"),

  (req, res, next) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }
    next();
  },
]

module.exports = { validateBidInput };