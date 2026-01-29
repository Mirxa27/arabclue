@extends('layouts.admin')

@section('title', 'Content Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark font-weight-bold mb-1">Content Management</h2>
                    <p class="text-muted mb-0">Manage website content, pages, and media</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                        <i class="fas fa-plus me-2"></i>Add Content
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-pages">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Published
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="published-content">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Draft Content
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="draft-content">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Media Files
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="media-files">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Content List</h6>
            <div class="d-flex gap-2">
                <select class="form-control form-control-sm" id="content-type-filter" style="width: auto;">
                    <option value="">All Types</option>
                    <option value="page">Pages</option>
                    <option value="blog">Blog Posts</option>
                    <option value="media">Media</option>
                </select>
                <select class="form-control form-control-sm" id="status-filter" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="contentTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Author</th>
                            <th>Last Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="content-table-body">
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Content Modal -->
<div class="modal fade" id="addContentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Content</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addContentForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="content-title">Title</label>
                                <input type="text" class="form-control" id="content-title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="content-type">Content Type</label>
                                <select class="form-control" id="content-type" required>
                                    <option value="">Select Type</option>
                                    <option value="page">Page</option>
                                    <option value="blog">Blog Post</option>
                                    <option value="media">Media</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content-slug">URL Slug</label>
                        <input type="text" class="form-control" id="content-slug">
                    </div>
                    <div class="form-group">
                        <label for="content-description">Content</label>
                        <textarea class="form-control" id="content-description" rows="6"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="content-status">Status</label>
                                <select class="form-control" id="content-status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="content-featured-image">Featured Image</label>
                                <input type="file" class="form-control" id="content-featured-image" accept="image/*">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveContent()">Save Content</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadContentStats();
    loadContentTable();
    
    // Filter handlers
    document.getElementById('content-type-filter').addEventListener('change', loadContentTable);
    document.getElementById('status-filter').addEventListener('change', loadContentTable);
});

function loadContentStats() {
    // Simulate loading content stats
    setTimeout(() => {
        document.getElementById('total-pages').textContent = '24';
        document.getElementById('published-content').textContent = '18';
        document.getElementById('draft-content').textContent = '6';
        document.getElementById('media-files').textContent = '142';
    }, 500);
}

function loadContentTable() {
    const tbody = document.getElementById('content-table-body');
    const typeFilter = document.getElementById('content-type-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    // Show loading
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </td>
        </tr>
    `;
    
    // Simulate API call
    setTimeout(() => {
        const sampleData = [
            {
                id: 1,
                title: 'About Us',
                type: 'page',
                status: 'published',
                author: 'Admin User',
                updated_at: '2024-01-15 10:30:00'
            },
            {
                id: 2,
                title: 'Terms of Service',
                type: 'page',
                status: 'published',
                author: 'Admin User',
                updated_at: '2024-01-14 16:45:00'
            },
            {
                id: 3,
                title: 'Welcome to HabibiStay',
                type: 'blog',
                status: 'draft',
                author: 'Content Manager',
                updated_at: '2024-01-13 09:15:00'
            }
        ];
        
        let filteredData = sampleData;
        if (typeFilter) {
            filteredData = filteredData.filter(item => item.type === typeFilter);
        }
        if (statusFilter) {
            filteredData = filteredData.filter(item => item.status === statusFilter);
        }
        
        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No content found</td></tr>';
            return;
        }
        
        tbody.innerHTML = filteredData.map(content => `
            <tr>
                <td>${content.title}</td>
                <td><span class="badge badge-info">${content.type}</span></td>
                <td>
                    <span class="badge badge-${content.status === 'published' ? 'success' : 'warning'}">
                        ${content.status}
                    </span>
                </td>
                <td>${content.author}</td>
                <td>${new Date(content.updated_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editContent(${content.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteContent(${content.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }, 800);
}

function saveContent() {
    const title = document.getElementById('content-title').value;
    const type = document.getElementById('content-type').value;
    
    if (!title || !type) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Simulate saving
    setTimeout(() => {
        alert('Content saved successfully!');
        document.getElementById('addContentModal').querySelector('[data-bs-dismiss="modal"]').click();
        document.getElementById('addContentForm').reset();
        loadContentStats();
        loadContentTable();
    }, 1000);
}

function editContent(id) {
    alert(`Edit content functionality would be implemented for content ID: ${id}`);
}

function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content?')) {
        setTimeout(() => {
            alert('Content deleted successfully!');
            loadContentStats();
            loadContentTable();
        }, 500);
    }
}
</script>
@endsection
