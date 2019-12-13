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

use Throwable;
use Sqlite3;

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
    private Sqlite3 $_database;

    /**
     * Load the Sqlite3 database object.
     *
     * @param Sqlite3 $database the database object.
     *
     * @return void
     */
    public function loadDb(Sqlite3 $database)
    {
        $this->_database = $database;
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
            $results = $callback($this->_database);
            $this->commitTransaction();
        } catch (Throwable $throwable) {
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
        $this->_database->exec('BEGIN');
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollbackTransaction(): void
    {
        $this->_database->exec('ROLLBACK');
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction(): void
    {
        $this->_database->exec('COMMIT');
    }
}
