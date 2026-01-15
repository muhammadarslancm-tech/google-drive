<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\File;
use App\Models\Share;
use App\Models\Folder;

/**
 * File Controller
 */
class FileController extends Controller
{
    /**
     * Upload a file
     */
    public function upload(): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        if (!isset($_FILES['file'])) {
            $this->error('No file uploaded', 400);
        }

        $file = $_FILES['file'];
        $folderId = $this->request->get('folder_id');

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error('File upload error', 400);
        }

        $config = require __DIR__ . '/../../config/app.php';
        
        if ($file['size'] > $config['max_file_size']) {
            $this->error('File size exceeds maximum allowed size', 400);
        }

        // Create upload directory if it doesn't exist
        $uploadDir = $config['uploads_path'];
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $this->error('Failed to create upload directory', 500);
            }
        }
        
        // Ensure directory is writable
        if (!is_writable($uploadDir)) {
            // Try to make it writable
            @chmod($uploadDir, 0777);
            if (!is_writable($uploadDir)) {
                $this->error('Upload directory is not writable. Please check permissions.', 500);
            }
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : 'Unknown error';
            $this->error('Failed to save file: ' . $errorMsg, 500);
        }
        
        // Ensure file has proper permissions
        @chmod($filePath, 0644);

        // Create file record
        $fileModel = new File();
        $fileId = $fileModel->createFile(
            $user['_id'],
            $file['name'],
            $filename,
            $file['size'],
            $file['type'],
            $folderId
        );

        if (!$fileId) {
            unlink($filePath); // Clean up
            $this->error('Failed to create file record', 500);
        }

        $fileData = $fileModel->findById($fileId);
        
        $this->success(['file' => $fileData], 'File uploaded successfully', 201);
    }

    /**
     * Get files
     */
    public function index(): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $folderId = $this->request->get('folder_id');
        $includeTrashed = $this->request->get('trashed') === 'true';

        $fileModel = new File();
        
        if ($includeTrashed) {
            $files = $fileModel->getTrashedFiles($user['_id']);
        } else {
            $files = $fileModel->getUserFiles($user['_id'], $folderId);
        }

        // Get shared files
        $shareModel = new Share();
        $sharedFiles = $shareModel->getSharedWithUser($user['_id'], 'file');
        
        foreach ($sharedFiles as $share) {
            if (!isset($share['resource_id'])) {
                continue;
            }
            $sharedFile = $fileModel->findById($share['resource_id']);
            if ($sharedFile) {
                $sharedFile['shared'] = true;
                $sharedFile['permission'] = $share['permission'] ?? 'read';
                $files[] = $sharedFile;
            }
        }

        $this->success(['files' => $files]);
    }

    /**
     * Get a single file
     */
    public function show(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $file = $fileModel->findById($id);

        if (!$file) {
            $this->error('File not found', 404);
        }

        // Check ownership or share
        $hasAccess = false;
        if ($file['user_id'] === $user['_id']) {
            $hasAccess = true;
        } else {
            $shareModel = new Share();
            $hasAccess = $shareModel->userHasAccess($id, 'file', $user['_id']);
        }

        if (!$hasAccess) {
            $this->error('Access denied', 403);
        }

        $this->success(['file' => $file]);
    }

    /**
     * Download a file
     */
    public function download(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $file = $fileModel->findById($id);

        if (!$file) {
            $this->error('File not found', 404);
        }

        // Check ownership or share
        $hasAccess = false;
        if ($file['user_id'] === $user['_id']) {
            $hasAccess = true;
        } else {
            $shareModel = new Share();
            $hasAccess = $shareModel->userHasAccess($id, 'file', $user['_id'], Share::PERMISSION_READ);
        }

        if (!$hasAccess) {
            $this->error('Access denied', 403);
        }

        $config = require __DIR__ . '/../../config/app.php';
        $filePath = $config['uploads_path'] . '/' . $file['path'];

        if (!file_exists($filePath)) {
            $this->error('File not found on disk', 404);
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
        header('Content-Length: ' . $file['size']);
        readfile($filePath);
        exit;
    }

    /**
     * Update a file
     */
    public function update(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $data = $this->request->all();

        $fileModel = new File();
        
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['folder_id'])) {
            $updateData['folder_id'] = $data['folder_id'];
        }

        if (empty($updateData)) {
            $this->error('No data to update', 400);
        }

        $updated = $fileModel->updateFile($id, $user['_id'], $updateData);

        if (!$updated) {
            $this->error('File not found or access denied', 404);
        }

        $file = $fileModel->findById($id);
        $this->success(['file' => $file], 'File updated successfully');
    }

    /**
     * Delete a file (soft delete)
     */
    public function delete(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $deleted = $fileModel->trashFile($id, $user['_id']);

        if (!$deleted) {
            $this->error('File not found or access denied', 404);
        }

        $this->success([], 'File moved to trash');
    }

    /**
     * Restore a file from trash
     */
    public function restore(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $restored = $fileModel->restoreFile($id, $user['_id']);

        if (!$restored) {
            $this->error('File not found or not in trash', 404);
        }

        $this->success([], 'File restored successfully');
    }

    /**
     * Permanently delete a file
     */
    public function permanentDelete(string $id): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $file = $fileModel->findById($id);

        if (!$file || $file['user_id'] !== $user['_id'] || !$file['is_trashed']) {
            $this->error('File not found or not in trash', 404);
        }

        // Delete physical file
        $config = require __DIR__ . '/../../config/app.php';
        $filePath = $config['uploads_path'] . '/' . $file['path'];
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete record
        $fileModel->delete($id);

        $this->success([], 'File permanently deleted');
    }

    /**
     * Get storage stats
     */
    public function storage(): void
    {
        $user = $this->request->user();
        
        if (!$user || !isset($user['_id'])) {
            $this->error('User not authenticated', 401, ['auth' => 'Authentication required']);
        }
        
        $fileModel = new File();
        $used = $fileModel->getUserStorageSize($user['_id']);
        $total = 100 * 1024 * 1024 * 1024; // 100GB default

        $this->success([
            'used' => $used,
            'total' => $total,
            'available' => $total - $used,
            'percentage' => round(($used / $total) * 100, 2)
        ]);
    }
}
