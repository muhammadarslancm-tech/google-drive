<?php

namespace App\Core;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Exception\Exception as MongoDBException;

/**
 * Database Singleton Class
 * Manages MongoDB connection using Singleton pattern
 * Uses MongoDB Driver API directly (no external library required)
 */
class Database
{
    private static ?Database $instance = null;
    private ?Manager $manager = null;
    private string $databaseName;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->databaseName = $config['database'];
        
        // Build connection string
        if (!empty($config['username']) && !empty($config['password'])) {
            $connectionString = sprintf(
                'mongodb://%s:%s@%s:%d/%s',
                urlencode($config['username']),
                urlencode($config['password']),
                $config['host'],
                $config['port'],
                $config['database']
            );
        } else {
            $connectionString = sprintf(
                'mongodb://%s:%d/%s',
                $config['host'],
                $config['port'],
                $config['database']
            );
        }

        try {
            $this->manager = new Manager($connectionString);
        } catch (MongoDBException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
