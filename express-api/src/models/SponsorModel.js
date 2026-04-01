const {pool} = require('../config/database');

class SponsorModel {
    static async findById(sponsorId) {
        const [rows] = await pool.execute(
            'SELECT * FROM sponsors WHERE id = ?',
            [sponsorId]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findByName(sponsorName) {
        const [rows] = await pool.execute(
            'SELECT * FROM sponsors WHERE name = ?',
            [sponsorName]
        );
        return rows.length > 0 ? rows[0] : null;
    }

    static async findAll(sponsorType = null){
        if(sponsorType){
            const [rows] = await pool.execute(
                'SELECT * FROM sponsors WHERE sponsor_type = ? ORDER BY sponsor_name ASC',
                [sponsorType]
            );
            return rows;
        }

        const [rows] = await pool.execute(
            'SELECT * FROM sponsors ORDER BY sponsor_name ASC'
        );
        return rows;
    }

    static async create(data){
        const {sponsor_name, sponsor_type, website_url} = data;
        const [result] = await pool.execute(
            'INSERT INTO sponsors (sponsor_name, sponsor_type, website_url) VALUES (?, ?, ?)',
            [sponsor_name, sponsor_type, website_url]
        );
        return {
            id: result.insertId,
            sponsor_name,
            sponsor_type,
            website_url,
        };
    }

}
module.exports = SponsorModel;