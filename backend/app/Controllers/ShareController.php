<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Share;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;

/**
 * Share Controller
 */
class ShareController extends Controller
{
    /**
     * Share a file or folder
     */
    public function create(): void
    {
        $user = $this->request->user();
        $data = $this->request->all();
        
        $errors = $this->validateRequired($data, ['resource_id', 'resource_type', 'shared_with_email', 'permission']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate permission
        if (!in_array($data['permission'], [Share::PERMISSION_READ, Share::PERMISSION_WRITE])) {
            $this->error('Invalid permission. Must be "read" or "write"', 400);
        }

        // Validate resource type
        if (!in_array($data['resource_type'], ['file', 'folder'])) {
            $this->error('Invalid resource type. Must be "file" or "folder"', 400);
        }

        // Find user to share with
        $userModel = new User();
        $sharedWithUser = $userModel->findByEmail($data['shared_with_email']);

        if (!$sharedWithUser) {
            $this->error('User not found', 404);
        }

        if ($sharedWithUser['_id'] === $user['_id']) {
            $this->error('Cannot share with yourself', 400);
        }

        // Verify ownership
        if ($data['resource_type'] === 'file') {
            $fileModel = new File();
            $resource = $fileModel->findById($data['resource_id']);
        } else {
            $folderModel = new Folder();
            $resource = $folderModel->findById($data['resource_id']);
        }

        if (!$resource || $resource['user_id'] !== $user['_id']) {
            $this->error('Resource not found or access denied', 404);
        }

        // Create share
        $shareModel = new Share();
        $shareId = $shareModel->createShare(
            $data['resource_id'],
            $data['resource_type'],
            $user['_id'],
            $sharedWithUser['_id'],
            $data['permission']
        );

        if (!$shareId) {
            $this->error('Failed to create share', 500);
        }

        $share = $shareModel->findById($shareId);
        
        $this->success(['share' => $share], 'Resource shared successfully', 201);
    }

    /**
     * Get shares for a resource
     */
    public function resourceShares(string $resourceId, string $resourceType): void
    {
        $user = $this->request->user();
        
        // Verify ownership
        if ($resourceType === 'file') {
            $fileModel = new File();
            $resource = $fileModel->findById($resourceId);
        } else {
            $folderModel = new Folder();
            $resource = $folderModel->findById($resourceId);
        }

        if (!$resource || $resource['user_id'] !== $user['_id']) {
            $this->error('Resource not found or access denied', 404);
        }

        $shareModel = new Share();
        $shares = $shareModel->getResourceShares($resourceId, $resourceType);

        // Get user details for each share
        $userModel = new User();
        foreach ($shares as &$share) {
            $sharedUser = $userModel->findByIdSafe($share['shared_with_id']);
            $share['shared_with'] = $sharedUser;
        }

        $this->success(['shares' => $shares]);
    }

    /**
     * Get resources shared with me
     */
    public function sharedWithMe(): void
    {
        $user = $this->request->user();
        
        $shareModel = new Share();
        $shares = $shareModel->getSharedWithUser($user['_id']);

        $fileModel = new File();
        $folderModel = new Folder();
        $userModel = new User();

        $resources = [];
        foreach ($shares as $share) {
            if ($share['resource_type'] === 'file') {
                $resource = $fileModel->findById($share['resource_id']);
            } else {
                $resource = $folderModel->findById($share['resource_id']);
            }

            if ($resource) {
                $owner = $userModel->findByIdSafe($share['owner_id']);
                $resource['owner'] = $owner;
                $resource['permission'] = $share['permission'];
                $resources[] = $resource;
            }
        }

        $this->success(['resources' => $resources]);
    }

    /**
     * Get resources shared by me
     */
    public function sharedByMe(): void
    {
        $user = $this->request->user();
        
        $shareModel = new Share();
        $shares = $shareModel->getSharedByUser($user['_id']);

        $fileModel = new File();
        $folderModel = new Folder();
        $userModel = new User();

        $resources = [];
        foreach ($shares as $share) {
            if ($share['resource_type'] === 'file') {
                $resource = $fileModel->findById($share['resource_id']);
            } else {
                $resource = $folderModel->findById($share['resource_id']);
            }

            if ($resource) {
                $sharedUser = $userModel->findByIdSafe($share['shared_with_id']);
                $resource['shared_with'] = $sharedUser;
                $resource['permission'] = $share['permission'];
                $resources[] = $resource;
            }
        }

        $this->success(['resources' => $resources]);
    }

    /**
     * Delete a share
     */
    public function delete(string $id): void
    {
        $user = $this->request->user();
        
        $shareModel = new Share();
        $deleted = $shareModel->deleteShare($id, $user['_id']);

        if (!$deleted) {
            $this->error('Share not found or access denied', 404);
        }

        $this->success([], 'Share removed successfully');
    }
}
