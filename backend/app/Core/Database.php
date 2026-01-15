<?php

namespace App\Core;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;

/**
 * Database Singleton Class
 * Manages MongoDB connection using Singleton pattern
 */
class Database
{
    private static ?Database $instance = null;
    private ?MongoDatabase $database = null;
    private ?Client $client = null;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        
        // Build connection string
        if (!empty($config['username']) && !empty($config['password'])) {
            $connectionString = sprintf(
                'mongodb://%s:%s@%s:%d/%s',
                $config['username'],
                $config['password'],
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
            $this->client = new Client($connectionString);
            $this->database = $this->client->selectDatabase($config['database']);
        } catch (\Exception $e) {
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

    public function getDatabase(): MongoDatabase
    {
        return $this->database;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCollection(string $collectionName): \MongoDB\Collection
    {
        return $this->database->selectCollection($collectionName);
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
