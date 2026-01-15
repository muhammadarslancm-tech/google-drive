/**
 * Main Application JavaScript
 */

const API_BASE_URL = '/api';

// Axios interceptor for authentication
axios.interceptors.request.use(config => {
    if (!(config.data instanceof FormData)) {
        config.headers['Content-Type'] = 'application/json';
    }
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Axios interceptor for error handling
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login.php';
        }
        return Promise.reject(error);
    }
);

// Application State
let currentView = 'files';
let currentFolderId = null;
let currentUser = null;

// Initialize app
$(document).ready(async function() {
    // Verify authentication before loading dashboard
    await verifyAuthentication();
    
    // Get user from localStorage
    const user = localStorage.getItem('user');
    if (user) {
        currentUser = JSON.parse(user);
        displayUserInfo(currentUser);
    }
    
    // Load initial view
    loadFiles();
    
    // Event listeners
    $('.sidebar-menu a[data-view]').on('click', function(e) {
        e.preventDefault();
        const view = $(this).data('view');
        switchView(view);
    });

    $('#createFolderBtn').on('click', showCreateFolderModal);
    $('#uploadFileBtn').on('click', () => $('#fileInput').click());
    $('#fileInput').on('change', handleFileUpload);
    $('#logoutBtn, #logoutBtn2').on('click', handleLogout);
    $('#searchInput').on('keyup', handleSearch);
});

// Switch between views
function switchView(view) {
    currentView = view;
    $('.sidebar-menu a').removeClass('active');
    $(`.sidebar-menu a[data-view="${view}"]`).addClass('active');
    
    $('.view').hide();
    
    switch(view) {
        case 'files':
            $('#filesView').show();
            loadFiles();
            break;
        case 'folders':
            $('#foldersView').show();
            loadFolders();
            break;
        case 'shared':
            $('#sharedView').show();
            loadShared();
            break;
        case 'trash':
            $('#trashView').show();
            loadTrash();
            break;
        case 'storage':
            $('#storageView').show();
            loadStorage();
            break;
    }
}

// Load files
function loadFiles(folderId = null) {
    currentFolderId = folderId;
    const url = folderId ? `${API_BASE_URL}/files?folder_id=${folderId}` : `${API_BASE_URL}/files`;
    
    $('#filesList').html('<div class="loading">Loading files...</div>');
    
    axios.get(url)
        .then(response => {
            const files = response.data.data.files || [];
            displayFiles(files);
        })
        .catch(error => {
            $('#filesList').html('<div class="empty-state">Error loading files</div>');
            console.error('Error loading files:', error);
        });
}

// Display files
function displayFiles(files) {
    if (files.length === 0) {
        $('#filesList').html('<div class="empty-state">No files found</div>');
        return;
    }

    let html = '<div class="row">';
    
    files.forEach(file => {
        const icon = getFileIcon(file.mime_type || '');
        const size = formatFileSize(file.size);
        const date = new Date(file.created_at).toLocaleDateString();
        
        html += `
            <div class="col-md-3 mb-3">
                <div class="card file-item" data-id="${file._id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <i class="${icon}" style="font-size: 2rem; color: #3b76e1;"></i>
                                <h6 class="mt-2 mb-1">${escapeHtml(file.name)}</h6>
                                <small class="text-muted">${size} • ${date}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" data-bs-toggle="dropdown">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="downloadFile('${file._id}')">Download</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="shareFile('${file._id}')">Share</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="renameFile('${file._id}', '${escapeHtml(file.name)}')">Rename</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile('${file._id}')">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    $('#filesList').html(html);
}

// Load folders
function loadFolders() {
    $('#foldersList').html('<div class="loading">Loading folders...</div>');
    
    axios.get(`${API_BASE_URL}/folders`)
        .then(response => {
            const folders = response.data.data.folders || [];
            displayFolders(folders);
        })
        .catch(error => {
            $('#foldersList').html('<div class="empty-state">Error loading folders</div>');
            console.error('Error loading folders:', error);
        });
}

// Display folders
function displayFolders(folders) {
    if (folders.length === 0) {
        $('#foldersList').html('<div class="empty-state">No folders found</div>');
        return;
    }

    let html = '<div class="row">';
    
    folders.forEach(folder => {
        const date = new Date(folder.created_at).toLocaleDateString();
        
        html += `
            <div class="col-md-3 mb-3">
                <div class="card folder-item" data-id="${folder._id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <i class="mdi mdi-folder" style="font-size: 2rem; color: #ffc107;"></i>
                                <h6 class="mt-2 mb-1">${escapeHtml(folder.name)}</h6>
                                <small class="text-muted">${date}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" data-bs-toggle="dropdown">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="openFolder('${folder._id}')">Open</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="shareFolder('${folder._id}')">Share</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="renameFolder('${folder._id}', '${escapeHtml(folder.name)}')">Rename</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFolder('${folder._id}')">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    $('#foldersList').html(html);
}

// Load shared files
function loadShared() {
    $('#sharedList').html('<div class="loading">Loading shared files...</div>');
    
    axios.get(`${API_BASE_URL}/shares/with-me`)
        .then(response => {
            const resources = response.data.data.resources || [];
            displayShared(resources);
        })
        .catch(error => {
            $('#sharedList').html('<div class="empty-state">Error loading shared files</div>');
            console.error('Error loading shared files:', error);
        });
}

// Display shared files
function displayShared(resources) {
    if (resources.length === 0) {
        $('#sharedList').html('<div class="empty-state">No shared files</div>');
        return;
    }

    let html = '<div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Type</th><th>Owner</th><th>Permission</th><th>Actions</th></tr></thead><tbody>';
    
    resources.forEach(resource => {
        const type = resource.folder_id !== undefined ? 'Folder' : 'File';
        const owner = resource.owner ? `${resource.owner.first_name} ${resource.owner.last_name}` : 'Unknown';
        
        html += `
            <tr>
                <td>${escapeHtml(resource.name)}</td>
                <td>${type}</td>
                <td>${escapeHtml(owner)}</td>
                <td><span class="badge bg-info">${resource.permission}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="downloadFile('${resource._id}')">Download</button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#sharedList').html(html);
}

// Load trash
function loadTrash() {
    $('#trashList').html('<div class="loading">Loading trash...</div>');
    
    Promise.all([
        axios.get(`${API_BASE_URL}/files?trashed=true`),
        axios.get(`${API_BASE_URL}/folders?trashed=true`)
    ])
    .then(([filesResponse, foldersResponse]) => {
        const files = filesResponse.data.data.files || [];
        const folders = foldersResponse.data.data.folders || [];
        displayTrash(files, folders);
    })
    .catch(error => {
        $('#trashList').html('<div class="empty-state">Error loading trash</div>');
        console.error('Error loading trash:', error);
    });
}

// Display trash
function displayTrash(files, folders) {
    if (files.length === 0 && folders.length === 0) {
        $('#trashList').html('<div class="empty-state">Trash is empty</div>');
        return;
    }

    let html = '<div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Type</th><th>Deleted</th><th>Actions</th></tr></thead><tbody>';
    
    files.forEach(file => {
        html += `
            <tr>
                <td>${escapeHtml(file.name)}</td>
                <td>File</td>
                <td>${new Date(file.trashed_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="restoreFile('${file._id}')">Restore</button>
                    <button class="btn btn-sm btn-danger" onclick="permanentDeleteFile('${file._id}')">Delete Permanently</button>
                </td>
            </tr>
        `;
    });
    
    folders.forEach(folder => {
        html += `
            <tr>
                <td>${escapeHtml(folder.name)}</td>
                <td>Folder</td>
                <td>${new Date(folder.trashed_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="restoreFolder('${folder._id}')">Restore</button>
                    <button class="btn btn-sm btn-danger" onclick="permanentDeleteFolder('${folder._id}')">Delete Permanently</button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#trashList').html(html);
}

// Load storage stats
function loadStorage() {
    $('#storageStats').html('<div class="loading">Loading storage stats...</div>');
    
    axios.get(`${API_BASE_URL}/files/storage/stats`)
        .then(response => {
            const stats = response.data.data;
            displayStorage(stats);
        })
        .catch(error => {
            $('#storageStats').html('<div class="empty-state">Error loading storage stats</div>');
            console.error('Error loading storage stats:', error);
        });
}

// Display storage stats
function displayStorage(stats) {
    const usedGB = (stats.used / (1024 * 1024 * 1024)).toFixed(2);
    const totalGB = (stats.total / (1024 * 1024 * 1024)).toFixed(2);
    const availableGB = (stats.available / (1024 * 1024 * 1024)).toFixed(2);
    
    const html = `
        <h6>Storage Usage</h6>
        <div class="d-flex justify-content-between mb-2">
            <span>${usedGB} GB of ${totalGB} GB used</span>
            <span>${stats.percentage}%</span>
        </div>
        <div class="storage-bar">
            <div class="storage-bar-fill" style="width: ${stats.percentage}%"></div>
        </div>
        <small class="text-muted">${availableGB} GB available</small>
    `;
    
    $('#storageStats').html(html);
}

// File operations
function handleFileUpload(e) {
    const files = e.target.files;
    if (files.length === 0) return;

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('file', files[i]);
    }
    
    if (currentFolderId) {
        formData.append('folder_id', currentFolderId);
    }

    axios.post(`${API_BASE_URL}/files/upload`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        alert('File uploaded successfully');
        loadFiles(currentFolderId);
        $('#fileInput').val('');
    })
    .catch(error => {
        alert('Error uploading file: ' + (error.response?.data?.message || error.message));
    });
}

function downloadFile(fileId) {
    window.open(`${API_BASE_URL}/files/${fileId}/download`, '_blank');
}

function deleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this file?')) return;
    
    axios.delete(`${API_BASE_URL}/files/${fileId}`)
        .then(() => {
            alert('File moved to trash');
            loadFiles(currentFolderId);
        })
        .catch(error => {
            alert('Error deleting file: ' + (error.response?.data?.message || error.message));
        });
}

function restoreFile(fileId) {
    axios.post(`${API_BASE_URL}/files/${fileId}/restore`)
        .then(() => {
            alert('File restored');
            loadTrash();
        })
        .catch(error => {
            alert('Error restoring file: ' + (error.response?.data?.message || error.message));
        });
}

function permanentDeleteFile(fileId) {
    if (!confirm('Are you sure you want to permanently delete this file? This action cannot be undone.')) return;
    
    axios.delete(`${API_BASE_URL}/files/${fileId}/permanent`)
        .then(() => {
            alert('File permanently deleted');
            loadTrash();
        })
        .catch(error => {
            alert('Error deleting file: ' + (error.response?.data?.message || error.message));
        });
}

function renameFile(fileId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (!newName || newName === currentName) return;
    
    axios.put(`${API_BASE_URL}/files/${fileId}`, { name: newName })
        .then(() => {
            alert('File renamed');
            loadFiles(currentFolderId);
        })
        .catch(error => {
            alert('Error renaming file: ' + (error.response?.data?.message || error.message));
        });
}

function shareFile(fileId) {
    const email = prompt('Enter email to share with:');
    if (!email) return;
    
    const permission = confirm('Allow write access? (OK for write, Cancel for read only)') ? 'write' : 'read';
    
    axios.post(`${API_BASE_URL}/shares`, {
        resource_id: fileId,
        resource_type: 'file',
        shared_with_email: email,
        permission: permission
    })
    .then(() => {
        alert('File shared successfully');
    })
    .catch(error => {
        alert('Error sharing file: ' + (error.response?.data?.message || error.message));
    });
}

// Folder operations
function showCreateFolderModal() {
    const name = prompt('Enter folder name:');
    if (!name) return;
    
    const data = { name: name };
    if (currentFolderId) {
        data.parent_id = currentFolderId;
    }
    
    axios.post(`${API_BASE_URL}/folders`, data)
        .then(() => {
            alert('Folder created successfully');
            loadFolders();
            if (currentView === 'files') {
                loadFiles(currentFolderId);
            }
        })
        .catch(error => {
            alert('Error creating folder: ' + (error.response?.data?.message || error.message));
        });
}

function openFolder(folderId) {
    currentView = 'files';
    loadFiles(folderId);
}

function deleteFolder(folderId) {
    if (!confirm('Are you sure you want to delete this folder?')) return;
    
    axios.delete(`${API_BASE_URL}/folders/${folderId}`)
        .then(() => {
            alert('Folder moved to trash');
            loadFolders();
        })
        .catch(error => {
            alert('Error deleting folder: ' + (error.response?.data?.message || error.message));
        });
}

function restoreFolder(folderId) {
    axios.post(`${API_BASE_URL}/folders/${folderId}/restore`)
        .then(() => {
            alert('Folder restored');
            loadTrash();
        })
        .catch(error => {
            alert('Error restoring folder: ' + (error.response?.data?.message || error.message));
        });
}

function permanentDeleteFolder(folderId) {
    if (!confirm('Are you sure you want to permanently delete this folder? This action cannot be undone.')) return;
    
    axios.delete(`${API_BASE_URL}/folders/${folderId}/permanent`)
        .then(() => {
            alert('Folder permanently deleted');
            loadTrash();
        })
        .catch(error => {
            alert('Error deleting folder: ' + (error.response?.data?.message || error.message));
        });
}

function renameFolder(folderId, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (!newName || newName === currentName) return;
    
    axios.put(`${API_BASE_URL}/folders/${folderId}`, { name: newName })
        .then(() => {
            alert('Folder renamed');
            loadFolders();
        })
        .catch(error => {
            alert('Error renaming folder: ' + (error.response?.data?.message || error.message));
        });
}

function shareFolder(folderId) {
    const email = prompt('Enter email to share with:');
    if (!email) return;
    
    const permission = confirm('Allow write access? (OK for write, Cancel for read only)') ? 'write' : 'read';
    
    axios.post(`${API_BASE_URL}/shares`, {
        resource_id: folderId,
        resource_type: 'folder',
        shared_with_email: email,
        permission: permission
    })
    .then(() => {
        alert('Folder shared successfully');
    })
    .catch(error => {
        alert('Error sharing folder: ' + (error.response?.data?.message || error.message));
    });
}

// Authentication verification
async function verifyAuthentication() {
    const token = localStorage.getItem('token');
    const user = localStorage.getItem('user');
    
    if (!token || !user) {
        window.location.href = '/login.php';
        return;
    }

    try {
        const response = await axios.get(`${API_BASE_URL}/auth/me`);

        if (response.data?.success && response.data.data?.user) {
            localStorage.setItem('user', JSON.stringify(response.data.data.user));
            return true;
        } else {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login.php';
            return false;
        }
    } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login.php';
        return false;
    }
}

// Utility functions
function handleLogout() {
    if (!confirm('Are you sure you want to logout?')) return;
    
    const token = localStorage.getItem('token');
    
    if (token) {
        axios.post(`${API_BASE_URL}/auth/logout`)
            .finally(() => {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login.php';
            });
    } else {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login.php';
    }
}

function handleSearch() {
    // Implement search functionality
    const query = $('#searchInput').val();
    // TODO: Implement search API call
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'mdi mdi-image';
    if (mimeType.startsWith('video/')) return 'mdi mdi-video';
    if (mimeType.startsWith('audio/')) return 'mdi mdi-music';
    if (mimeType.includes('pdf')) return 'mdi mdi-file-pdf-box';
    if (mimeType.includes('word')) return 'mdi mdi-file-word-box';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'mdi mdi-file-excel-box';
    return 'mdi mdi-file-outline';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function displayUserInfo(user) {
    if (user) {
        const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User';
        const email = user.email || '';
        
        // Display user name in top bar
        $('#userNameDisplay').text(fullName);
        $('#userEmailDisplay').text(email);
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
