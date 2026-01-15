<?php

namespace App\Core;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception as MongoDBException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * Base Model Class
 * All models must extend this class
 * Uses MongoDB Driver API directly (no external library required)
 */
abstract class Model
{
    protected Manager $manager;
    protected string $databaseName;
    protected string $collectionName;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->manager = $db->getManager();
        $this->databaseName = $db->getDatabaseName();
    }

    /**
     * Get namespace (database.collection)
     */
    protected function getNamespace(): string
    {
        return $this->databaseName . '.' . $this->collectionName;
    }

    /**
     * Find a document by ID
     */
    public function findById(string $id): ?array
    {
        try {
            $query = new Query(['_id' => new ObjectId($id)]);
            $cursor = $this->manager->executeQuery($this->getNamespace(), $query);
            $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);
            
            $result = current($cursor->toArray());
            return $result ? $this->convertToArray($result) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Find documents by criteria
     */
    public function find(array $criteria = [], array $options = []): array
    {
        try {
            $query = new Query($criteria, $options);
            $cursor = $this->manager->executeQuery($this->getNamespace(), $query);
            $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);
            
            $results = [];
            foreach ($cursor as $document) {
                $results[] = $this->convertToArray($document);
            }
            
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Find one document by criteria
     */
    public function findOne(array $criteria = []): ?array
    {
        try {
            $query = new Query($criteria, ['limit' => 1]);
            $cursor = $this->manager->executeQuery($this->getNamespace(), $query);
            $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);
            
            $result = current($cursor->toArray());
            return $result ? $this->convertToArray($result) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a new document
     */
    public function create(array $data): ?string
    {
        try {
            // Ensure timestamps
            if (!isset($data['created_at'])) {
                $data['created_at'] = new UTCDateTime();
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = new UTCDateTime();
            }
            
            $bulk = new BulkWrite();
            $insertedId = $bulk->insert($data);
            $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            
            return $insertedId->__toString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update a document by ID
     */
    public function update(string $id, array $data): bool
    {
        try {
            $data['updated_at'] = new UTCDateTime();
            
            $bulk = new BulkWrite();
            $bulk->update(
                ['_id' => new ObjectId($id)],
                ['$set' => $data]
            );
            
            $result = $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            return $result->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete a document by ID
     */
    public function delete(string $id): bool
    {
        try {
            $bulk = new BulkWrite();
            $bulk->delete(['_id' => new ObjectId($id)]);
            
            $result = $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple documents
     */
    public function deleteMany(array $criteria): int
    {
        try {
            $bulk = new BulkWrite();
            $bulk->delete($criteria, ['limit' => false]);
            
            $result = $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count documents
     */
    public function count(array $criteria = []): int
    {
        try {
            $command = new Command([
                'count' => $this->collectionName,
                'query' => $criteria
            ]);
            
            $cursor = $this->manager->executeCommand($this->databaseName, $command);
            $result = current($cursor->toArray());
            
            return isset($result->n) ? (int)$result->n : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Convert MongoDB document to array
     */
    protected function convertToArray($document): array
    {
        if (!is_array($document)) {
            $document = (array)$document;
        }
        
        $array = [];
        
        foreach ($document as $key => $value) {
            if ($value instanceof ObjectId) {
                $array[$key] = $value->__toString();
            } elseif ($value instanceof UTCDateTime) {
                $array[$key] = $value->toDateTime()->format('Y-m-d H:i:s');
            } elseif (is_array($value)) {
                $array[$key] = $this->convertToArray($value);
            } else {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }
}
