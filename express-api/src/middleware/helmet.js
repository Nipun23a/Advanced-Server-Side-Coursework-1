const helmet = require('helmet');

const helmetMiddleware = helmet({
    contentSecurityPolicy : false,
});

module.exports = helmetMiddleware;