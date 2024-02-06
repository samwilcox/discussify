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
 * Custom exception class for the handling of database-related errors.
 * @class
 */
class DatabaseException extends Error {

    /**
     * Creates a new instance of the DatabaseException class.
     * @param {string} message - the error message.
     * @param {string} query  - the SQL query that caused the error (optional).
     * @param {Error} error  - The original error object (optional).
     */
    constructor(message, query, error) {
        super(message);
        this.name = this.constructor.name;
        this.query = query || null;
        this.error = error || null;
        
        Error.captureStackTrace(this, this.constructor);
    }
}