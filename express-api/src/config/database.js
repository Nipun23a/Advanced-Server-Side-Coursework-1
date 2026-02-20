const mysql = require('mysql2/promise');
const logger = require('../utils/logger');

const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT) || 3306,
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'coursework1',
    connectionLimit: parseInt(process.env.DB_CONNECTION_LIMIT) || 10,
    waitForConnections: true,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 10000
});

const testConnection = async () => {
    try {
        const connection = await pool.getConnection();
        logger.info("Database connection established successfully");
        connection.release();
    }catch (error){
        logger.error("Database connection failed: ",error.message);
        process.exit(1);
    }
}

module.exports = {pool,testConnection};

