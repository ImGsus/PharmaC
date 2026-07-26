<?php

namespace App\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Throwable;

class MigrateCommand extends BaseMigrateCommand
{
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        parent::__construct($migrator, $dispatcher);
    }

    public function handle()
    {
        $connectionName = $this->option('database') ?: config('database.default');
        $databaseName = $this->getDatabaseName($connectionName);

        try {
            if (! $this->databaseExists($connectionName)) {
                $message = "The configured database '{$databaseName}' on connection '{$connectionName}' does not exist. Would you like to create it?";

                if (! $this->confirm($message)) {
                    $this->error('Database creation aborted.');
                    return 1;
                }

                if (! $this->createDatabase($connectionName)) {
                    $this->error("Failed to create database '{$databaseName}'. Check your connection settings and try again.");
                    return 1;
                }

                $this->info("Database '{$databaseName}' created successfully.");
            }
        } catch (Throwable $e) {
            $this->error('Database connection failed: '.$e->getMessage());
            return 1;
        }

        return parent::handle();
    }

    protected function getDatabaseName(string $connectionName): string
    {
        return config("database.connections.{$connectionName}.database", '');
    }

    protected function databaseExists(string $connectionName): bool
    {
        try {
            DB::connection($connectionName)->getPdo();
            return true;
        } catch (QueryException $e) {
            $previous = $e->getPrevious();

            if ($previous instanceof PDOException) {
                if ($this->isMissingDatabaseError($previous)) {
                    return false;
                }
                if ($this->isConnectionError($previous)) {
                    throw new PDOException('Database server connection failed: '.$previous->getMessage(), (int) $previous->getCode(), $previous);
                }
            }

            throw $e;
        } catch (PDOException $e) {
            if ($this->isMissingDatabaseError($e)) {
                return false;
            }
            if ($this->isConnectionError($e)) {
                throw new PDOException('Database server connection failed: '.$e->getMessage(), (int) $e->getCode(), $e);
            }

            throw $e;
        }
    }

    protected function isMissingDatabaseError($exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'Unknown database')
            || str_contains($message, 'SQLSTATE[HY000] [1049]');
    }

    protected function isConnectionError($exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'SQLSTATE[HY000] [2002]')
            || str_contains($message, 'SQLSTATE[HY000] [2003]')
            || str_contains($message, 'SQLSTATE[HY000] [2007]');
    }

    protected function createDatabase(string $connectionName): bool
    {
        $config = config("database.connections.{$connectionName}");

        if (! is_array($config) || ($config['driver'] ?? null) !== 'mysql') {
            $this->error('Automatic database creation is only supported for MySQL connections.');
            return false;
        }

        try {
            $pdo = $this->createMysqlPdo($config);
            $database = $config['database'] ?? '';
            $charset = $config['charset'] ?? 'utf8mb4';
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

            $pdo->exec('CREATE DATABASE '.$this->quoteIdentifier($database).' CHARACTER SET '.$charset.' COLLATE '.$collation);

            return true;
        } catch (PDOException $e) {
            $this->error($e->getMessage());
            return false;
        }
    }

    protected function createMysqlPdo(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $socket = $config['unix_socket'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        if (! empty($socket)) {
            $dsn = "mysql:unix_socket={$socket};charset={$charset}";
        } else {
            $dsn = "mysql:host={$host};port={$port};charset={$charset}";
        }

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    protected function quoteIdentifier(string $value): string
    {
        return '`'.str_replace('`', '``', $value).'`';
    }
}
