<?php

namespace App\Core;

use MongoDB\Collection;
use MongoDB\BSON\ObjectId;

/**
 * Base Model Class
 * All models must extend this class
 */
abstract class Model
{
    protected Collection $collection;
    protected string $collectionName;

    public function __construct()
    {
        $this->collection = Database::getInstance()->getCollection($this->collectionName);
    }

    /**
     * Find a document by ID
     */
    public function findById(string $id): ?array
    {
        try {
            $result = $this->collection->findOne(['_id' => new ObjectId($id)]);
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
        $cursor = $this->collection->find($criteria, $options);
        $results = [];
        
        foreach ($cursor as $document) {
            $results[] = $this->convertToArray($document);
        }
        
        return $results;
    }

    /**
     * Find one document by criteria
     */
    public function findOne(array $criteria = []): ?array
    {
        $result = $this->collection->findOne($criteria);
        return $result ? $this->convertToArray($result) : null;
    }

    /**
     * Create a new document
     */
    public function create(array $data): ?string
    {
        $data['created_at'] = new \MongoDB\BSON\UTCDateTime();
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        
        $result = $this->collection->insertOne($data);
        return $result->getInsertedId()->__toString();
    }

    /**
     * Update a document by ID
     */
    public function update(string $id, array $data): bool
    {
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        
        try {
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $data]
            );
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
            $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Count documents
     */
    public function count(array $criteria = []): int
    {
        return $this->collection->countDocuments($criteria);
    }

    /**
     * Convert MongoDB document to array
     */
    protected function convertToArray($document): array
    {
        $array = [];
        
        foreach ($document as $key => $value) {
            if ($value instanceof ObjectId) {
                $array[$key] = $value->__toString();
            } elseif ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $array[$key] = $value->toDateTime()->format('Y-m-d H:i:s');
            } else {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }

    /**
     * Get collection instance
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
