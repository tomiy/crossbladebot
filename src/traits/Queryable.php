<?php
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Traits;

use Sqlite3;
use Throwable;

/**
 * Wraps a Sqlite3 connection with some helper functions.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
trait Queryable
{
    private $_db;

    /**
     * Load the Sqlite3 database object.
     *
     * @param Sqlite3 $db the database object.
     *
     * @return void
     */
    public function loadDb(Sqlite3 $db)
    {
        $this->_db = $db;
    }

    /**
     * Perform transactions wrapped in a fail-safe callback.
     *
     * @param callable $callback the callback containing the transactions.
     *
     * @return array
     */
    public function query(callable $callback): array
    {
        $results = [];
        $this->beginTransaction();

        try {
            $results = $callback($this->_db);
            $this->commitTransaction();
        } catch (Throwable $th) {
            $this->rollbackTransaction();
        } finally {
            return $results;
        }
    }

    /**
     * Signal the beginning of a transaction.
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->_db->exec('BEGIN');
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollbackTransaction(): void
    {
        $this->_db->exec('ROLLBACK');
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction(): void
    {
        $this->_db->exec('COMMIT');
    }
}
