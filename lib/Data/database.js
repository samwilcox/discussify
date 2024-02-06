/**
 * DOT.BB
 * By Sam Wilcox <sam@dot-bb.com
 * 
 * https://www.dot-bb.com
 * 
 * View the user-end license agreement at:
 * https://license.dot-bb.com
 */

/**
 * Database Class
 * 
 * This class represents the database for DOT.BB.
 * Contains all neccessary logic for database connection and management.
 * 
 * @class
 */

const handle = require('mysql2/promise');

class Database {
    constructor() {
        this.pool = handle.createPool({
            host: process.env.DB_HOST,
            ser: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            connectionLimit: 90
        });
    }

    /**
     * Executes a given SQL statement on the database along with optional
     * parameters.
     * @param {string} sql - the SQL statement to be executed.
     * @param {Array} params - An array of parameters to be used in the SQL statement (optional).
     * @returns {Promise<Array>} - A Promise that resolves to the query results.
     * @throws {DatabaseException} - Throws an DatabaseException if there is an issue executing the given SQL statment query.
     */
    async executeQuery(sql, params) {
        let connection;

        try {
            connection = await this.pool.getConnection();
            const [results, fields] = await connection.execute(sql, params);
            
            return results;
        } catch (error) {
            throw new DatabaseException('A database error occured', sql, error);
        } finally {
            if (connection) connection.release();
        }
    }

    /**
     * Closes the connection pool.
     * Call this method when the database interactions are complete.
     * 
     * @returns {Promise<void>} - A Promise that resolves when the pool is closed.
     */
    async closePool() {
        await this.pool.end();
    }

    /**
     * Retrieves the last inserted primary key value after an insert operation.
     * Supports the following database types:
     * - MySQL
     * - Microsoft SQL Server
     * - PostgreSQL
     * - SQLite
     * 
     * @returns {Promise<number|null>} - A Promise that resolves to the last inserted ID or null if not available.
     * @throws {DatabaseException} - Throws a DatabaseException if the database type is unsupported.
     */
    async getLastInsertId() {
        var query = null;

        if (process.env.DB_TYPE === 'mysql') {
            query = 'SELECT LAST_INSERT_ID() as lastId';
        } else if (process.env.DB_TYPE === 'postgresql') {
            query = 'SELECT lastval() as lastId';
        } else if (process.env.DB_TYPE === 'mssql') {
            query = 'SELECT SCOPE_IDENTITY() as lastId';
        } else if (process.env.DB_TYPE === 'sqlite') {
            query = 'SELECT last_insert_rowid() as lastId';
        } else {
            throw new DatabaseException('Unsupported database type');
        }

        const results = await this.executeQuery(query);

        return results.length > 0 ? results[0].lastId : null;
    }

    
}

module.exports = Database;