<?php

namespace App\Models;

use App\Core\Model;

/**
 * Share Model
 * Manages file and folder sharing
 */
class Share extends Model
{
    protected string $collectionName = 'shares';

    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';

    /**
     * Create a share
     */
    public function createShare(
        string $resourceId,
        string $resourceType,
        string $ownerId,
        string $sharedWithId,
        string $permission
    ): ?string {
        // Check if share already exists
        $existing = $this->findOne([
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
            'shared_with_id' => $sharedWithId
        ]);

        if ($existing) {
            // Update permission
            return $this->update($existing['_id'], ['permission' => $permission]) ? $existing['_id'] : null;
        }

        $data = [
            'resource_id' => $resourceId,
            'resource_type' => $resourceType, // 'file' or 'folder'
            'owner_id' => $ownerId,
            'shared_with_id' => $sharedWithId,
            'permission' => $permission,
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'updated_at' => new \MongoDB\BSON\UTCDateTime()
        ];

        return $this->create($data);
    }

    /**
     * Get shares for a resource
     */
    public function getResourceShares(string $resourceId, string $resourceType): array
    {
        return $this->find([
            'resource_id' => $resourceId,
            'resource_type' => $resourceType
        ]);
    }

    /**
     * Get resources shared with user
     */
    public function getSharedWithUser(string $userId, string $resourceType = null): array
    {
        $criteria = ['shared_with_id' => $userId];
        
        if ($resourceType) {
            $criteria['resource_type'] = $resourceType;
        }

        return $this->find($criteria);
    }

    /**
     * Get resources shared by user
     */
    public function getSharedByUser(string $userId): array
    {
        return $this->find(['owner_id' => $userId]);
    }

    /**
     * Delete share
     */
    public function deleteShare(string $shareId, string $userId): bool
    {
        $share = $this->findById($shareId);
        
        if (!$share || $share['owner_id'] !== $userId) {
            return false;
        }

        return $this->delete($shareId);
    }

    /**
     * Check if user has access to resource
     */
    public function userHasAccess(string $resourceId, string $resourceType, string $userId, string $requiredPermission = self::PERMISSION_READ): bool
    {
        $share = $this->findOne([
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
            'shared_with_id' => $userId
        ]);

        if (!$share) {
            return false;
        }

        // Write permission includes read
        if ($requiredPermission === self::PERMISSION_READ) {
            return true;
        }

        return $share['permission'] === self::PERMISSION_WRITE;
    }

    /**
     * Get user permission for resource
     */
    public function getUserPermission(string $resourceId, string $resourceType, string $userId): ?string
    {
        $share = $this->findOne([
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
            'shared_with_id' => $userId
        ]);

        return $share ? $share['permission'] : null;
    }
}
