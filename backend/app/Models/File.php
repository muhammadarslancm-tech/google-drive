<?php

namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

/**
 * File Model
 */
class File extends Model
{
    protected string $collectionName = 'files';

    /**
     * Create a new file record
     */
    public function createFile(
        string $userId,
        string $name,
        string $path,
        int $size,
        string $mimeType,
        ?string $folderId = null
    ): ?string {
        $data = [
            'user_id' => $userId,
            'name' => $name,
            'path' => $path,
            'size' => $size,
            'mime_type' => $mimeType,
            'folder_id' => $folderId,
            'is_trashed' => false,
            'trashed_at' => null,
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'updated_at' => new \MongoDB\BSON\UTCDateTime()
        ];

        return $this->create($data);
    }

    /**
     * Get files for user
     */
    public function getUserFiles(string $userId, ?string $folderId = null, bool $includeTrashed = false): array
    {
        $criteria = ['user_id' => $userId];
        
        if ($folderId === null) {
            $criteria['folder_id'] = null;
        } else {
            $criteria['folder_id'] = $folderId;
        }

        if (!$includeTrashed) {
            $criteria['is_trashed'] = false;
        }

        return $this->find($criteria, ['sort' => ['created_at' => -1]]);
    }

    /**
     * Get trashed files for user
     */
    public function getTrashedFiles(string $userId): array
    {
        return $this->find([
            'user_id' => $userId,
            'is_trashed' => true
        ], ['sort' => ['trashed_at' => -1]]);
    }

    /**
     * Soft delete file
     */
    public function trashFile(string $fileId, string $userId): bool
    {
        $file = $this->findById($fileId);
        
        if (!$file || $file['user_id'] !== $userId) {
            return false;
        }

        return $this->update($fileId, [
            'is_trashed' => true,
            'trashed_at' => new \MongoDB\BSON\UTCDateTime()
        ]);
    }

    /**
     * Restore file from trash
     */
    public function restoreFile(string $fileId, string $userId): bool
    {
        $file = $this->findById($fileId);
        
        if (!$file || $file['user_id'] !== $userId || !$file['is_trashed']) {
            return false;
        }

        return $this->update($fileId, [
            'is_trashed' => false,
            'trashed_at' => null
        ]);
    }

    /**
     * Check if user owns file
     */
    public function userOwnsFile(string $fileId, string $userId): bool
    {
        $file = $this->findById($fileId);
        return $file && $file['user_id'] === $userId;
    }

    /**
     * Update file
     */
    public function updateFile(string $fileId, string $userId, array $data): bool
    {
        if (!$this->userOwnsFile($fileId, $userId)) {
            return false;
        }

        return $this->update($fileId, $data);
    }

    /**
     * Get file size for user
     */
    public function getUserStorageSize(string $userId): int
    {
        $files = $this->find([
            'user_id' => $userId,
            'is_trashed' => false
        ]);

        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += $file['size'];
        }

        return $totalSize;
    }
}
