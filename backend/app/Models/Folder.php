<?php

namespace App\Models;

use App\Core\Model;

/**
 * Folder Model
 */
class Folder extends Model
{
    protected string $collectionName = 'folders';

    /**
     * Create a new folder
     */
    public function createFolder(string $userId, string $name, ?string $parentId = null): ?string
    {
        // Check if folder with same name exists in same parent
        $existing = $this->findOne([
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId,
            'is_trashed' => false
        ]);

        if ($existing) {
            return null; // Folder already exists
        }

        $data = [
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId,
            'is_trashed' => false,
            'trashed_at' => null,
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'updated_at' => new \MongoDB\BSON\UTCDateTime()
        ];

        return $this->create($data);
    }

    /**
     * Get folders for user
     */
    public function getUserFolders(string $userId, ?string $parentId = null, bool $includeTrashed = false): array
    {
        $criteria = ['user_id' => $userId];
        
        if ($parentId === null) {
            $criteria['parent_id'] = null;
        } else {
            $criteria['parent_id'] = $parentId;
        }

        if (!$includeTrashed) {
            $criteria['is_trashed'] = false;
        }

        return $this->find($criteria, ['sort' => ['created_at' => -1]]);
    }

    /**
     * Get trashed folders for user
     */
    public function getTrashedFolders(string $userId): array
    {
        return $this->find([
            'user_id' => $userId,
            'is_trashed' => true
        ], ['sort' => ['trashed_at' => -1]]);
    }

    /**
     * Get folder path (breadcrumb)
     */
    public function getFolderPath(string $folderId): array
    {
        $path = [];
        $currentId = $folderId;

        while ($currentId) {
            $folder = $this->findById($currentId);
            if (!$folder) {
                break;
            }
            array_unshift($path, $folder);
            $currentId = $folder['parent_id'] ?? null;
        }

        return $path;
    }

    /**
     * Soft delete folder
     */
    public function trashFolder(string $folderId, string $userId): bool
    {
        $folder = $this->findById($folderId);
        
        if (!$folder || $folder['user_id'] !== $userId) {
            return false;
        }

        return $this->update($folderId, [
            'is_trashed' => true,
            'trashed_at' => new \MongoDB\BSON\UTCDateTime()
        ]);
    }

    /**
     * Restore folder from trash
     */
    public function restoreFolder(string $folderId, string $userId): bool
    {
        $folder = $this->findById($folderId);
        
        if (!$folder || $folder['user_id'] !== $userId || !$folder['is_trashed']) {
            return false;
        }

        return $this->update($folderId, [
            'is_trashed' => false,
            'trashed_at' => null
        ]);
    }

    /**
     * Check if user owns folder
     */
    public function userOwnsFolder(string $folderId, string $userId): bool
    {
        $folder = $this->findById($folderId);
        return $folder && $folder['user_id'] === $userId;
    }

    /**
     * Update folder
     */
    public function updateFolder(string $folderId, string $userId, array $data): bool
    {
        if (!$this->userOwnsFolder($folderId, $userId)) {
            return false;
        }

        return $this->update($folderId, $data);
    }
}
