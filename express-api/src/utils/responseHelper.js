/* Send Success Response */

const sendSuccess = (res, data = {}, message = 'Operation completed successfully', status = 200) => {
    return res.status(status).json({
        success: true,
        data,
        message,
    });
};

const sendError = (res, code, message, status = 400, details = []) => {
    return res.status(status).json({
        success: false,
        error: {
            code,
            message,
            details,
        },
    });
};


module.exports = { sendSuccess, sendError };
