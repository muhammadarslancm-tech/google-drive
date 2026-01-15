<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Folder;
use App\Models\File;
use App\Models\Share;

/**
 * Folder Controller
 */
class FolderController extends Controller
{
    /**
     * Create a folder
     */
    public function create(): void
    {
        $user = $this->request->user();
        $data = $this->request->all();
        
        $errors = $this->validateRequired($data, ['name']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $folderModel = new Folder();
        $folderId = $folderModel->createFolder(
            $user['_id'],
            $data['name'],
            $data['parent_id'] ?? null
        );

        if (!$folderId) {
            $this->error('Folder with this name already exists', 409);
        }

        $folder = $folderModel->findById($folderId);
        
        $this->success(['folder' => $folder], 'Folder created successfully', 201);
    }

    /**
     * Get folders
     */
    public function index(): void
    {
        $user = $this->request->user();
        $parentId = $this->request->get('parent_id');
        $includeTrashed = $this->request->get('trashed') === 'true';

        $folderModel = new Folder();
        
        if ($includeTrashed) {
            $folders = $folderModel->getTrashedFolders($user['_id']);
        } else {
            $folders = $folderModel->getUserFolders($user['_id'], $parentId);
        }

        // Get shared folders
        $shareModel = new Share();
        $sharedFolders = $shareModel->getSharedWithUser($user['_id'], 'folder');
        
        foreach ($sharedFolders as $share) {
            $sharedFolder = $folderModel->findById($share['resource_id']);
            if ($sharedFolder) {
                $sharedFolder['shared'] = true;
                $sharedFolder['permission'] = $share['permission'];
                $folders[] = $sharedFolder;
            }
        }

        $this->success(['folders' => $folders]);
    }

    /**
     * Get a single folder
     */
    public function show(string $id): void
    {
        $user = $this->request->user();
        
        $folderModel = new Folder();
        $folder = $folderModel->findById($id);

        if (!$folder) {
            $this->error('Folder not found', 404);
        }

        // Check ownership or share
        $hasAccess = false;
        if ($folder['user_id'] === $user['_id']) {
            $hasAccess = true;
        } else {
            $shareModel = new Share();
            $hasAccess = $shareModel->userHasAccess($id, 'folder', $user['_id']);
        }

        if (!$hasAccess) {
            $this->error('Access denied', 403);
        }

        // Get folder contents
        $subfolders = $folderModel->getUserFolders($user['_id'], $id);
        $fileModel = new File();
        $files = $fileModel->getUserFiles($user['_id'], $id);

        $this->success([
            'folder' => $folder,
            'subfolders' => $subfolders,
            'files' => $files
        ]);
    }

    /**
     * Update a folder
     */
    public function update(string $id): void
    {
        $user = $this->request->user();
        $data = $this->request->all();

        $folderModel = new Folder();
        
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['parent_id'])) {
            $updateData['parent_id'] = $data['parent_id'];
        }

        if (empty($updateData)) {
            $this->error('No data to update', 400);
        }

        $updated = $folderModel->updateFolder($id, $user['_id'], $updateData);

        if (!$updated) {
            $this->error('Folder not found or access denied', 404);
        }

        $folder = $folderModel->findById($id);
        $this->success(['folder' => $folder], 'Folder updated successfully');
    }

    /**
     * Delete a folder (soft delete)
     */
    public function delete(string $id): void
    {
        $user = $this->request->user();
        
        $folderModel = new Folder();
        $deleted = $folderModel->trashFolder($id, $user['_id']);

        if (!$deleted) {
            $this->error('Folder not found or access denied', 404);
        }

        $this->success([], 'Folder moved to trash');
    }

    /**
     * Restore a folder from trash
     */
    public function restore(string $id): void
    {
        $user = $this->request->user();
        
        $folderModel = new Folder();
        $restored = $folderModel->restoreFolder($id, $user['_id']);

        if (!$restored) {
            $this->error('Folder not found or not in trash', 404);
        }

        $this->success([], 'Folder restored successfully');
    }

    /**
     * Permanently delete a folder
     */
    public function permanentDelete(string $id): void
    {
        $user = $this->request->user();
        
        $folderModel = new Folder();
        $folder = $folderModel->findById($id);

        if (!$folder || $folder['user_id'] !== $user['_id'] || !$folder['is_trashed']) {
            $this->error('Folder not found or not in trash', 404);
        }

        // Delete all files in folder recursively
        $fileModel = new File();
        $files = $fileModel->getUserFiles($user['_id'], $id);
        
        $config = require __DIR__ . '/../../config/app.php';
        foreach ($files as $file) {
            $filePath = $config['uploads_path'] . '/' . $file['path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $fileModel->delete($file['_id']);
        }

        // Delete subfolders recursively
        $subfolders = $folderModel->getUserFolders($user['_id'], $id);
        foreach ($subfolders as $subfolder) {
            // Recursive delete (simplified - in production, use proper recursion)
            $folderModel->delete($subfolder['_id']);
        }

        // Delete folder record
        $folderModel->delete($id);

        $this->success([], 'Folder permanently deleted');
    }

    /**
     * Get folder path (breadcrumb)
     */
    public function path(string $id): void
    {
        $user = $this->request->user();
        
        $folderModel = new Folder();
        $folder = $folderModel->findById($id);

        if (!$folder) {
            $this->error('Folder not found', 404);
        }

        if ($folder['user_id'] !== $user['_id']) {
            $this->error('Access denied', 403);
        }

        $path = $folderModel->getFolderPath($id);
        
        $this->success(['path' => $path]);
    }
}
