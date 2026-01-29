@extends('layouts.admin')

@section('title', 'Pages Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Pages Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.content.index') }}">Content</a></li>
                    <li class="breadcrumb-item active">Pages</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createPageModal">
                <i class="fas fa-plus"></i> Create New Page
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pages</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Published</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Draft</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Page Views</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">25.4K</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pages Management -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">Website Pages</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Search pages..." id="pageSearch">
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pagesTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Template</th>
                            <th>Views</th>
                            <th>Last Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home text-primary mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Home Page</div>
                                        <small class="text-muted">Main landing page</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/</code></td>
                            <td><span class="badge badge-success">Published</span></td>
                            <td>home.blade.php</td>
                            <td>12.5K</td>
                            <td>2 days ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">About Us</div>
                                        <small class="text-muted">Company information</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/about</code></td>
                            <td><span class="badge badge-success">Published</span></td>
                            <td>pages.about.blade.php</td>
                            <td>3.2K</td>
                            <td>1 week ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone text-success mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Contact Us</div>
                                        <small class="text-muted">Contact information and form</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/contact</code></td>
                            <td><span class="badge badge-success">Published</span></td>
                            <td>contact.blade.php</td>
                            <td>1.8K</td>
                            <td>3 days ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-contract text-warning mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Terms of Service</div>
                                        <small class="text-muted">Legal terms and conditions</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/terms</code></td>
                            <td><span class="badge badge-success">Published</span></td>
                            <td>legal.terms.blade.php</td>
                            <td>945</td>
                            <td>2 weeks ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt text-secondary mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Privacy Policy</div>
                                        <small class="text-muted">Privacy and data protection</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/privacy</code></td>
                            <td><span class="badge badge-success">Published</span></td>
                            <td>legal.privacy.blade.php</td>
                            <td>723</td>
                            <td>2 weeks ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-blog text-purple mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">Blog</div>
                                        <small class="text-muted">Latest news and articles</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>/blog</code></td>
                            <td><span class="badge badge-warning">Draft</span></td>
                            <td>pages.blog.blade.php</td>
                            <td>0</td>
                            <td>1 day ago</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" title="SEO">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Page Templates -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Page Templates</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                            <h6>Basic Page</h6>
                            <p class="text-muted small">Standard page template with header, content, and footer</p>
                            <button class="btn btn-sm btn-outline-primary">Use Template</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-3x text-success mb-3"></i>
                            <h6>Landing Page</h6>
                            <p class="text-muted small">Hero section with call-to-action and features</p>
                            <button class="btn btn-sm btn-outline-success">Use Template</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                            <h6>Contact Page</h6>
                            <p class="text-muted small">Contact form with location and information</p>
                            <button class="btn btn-sm btn-outline-info">Use Template</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Page Modal -->
<div class="modal fade" id="createPageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Page</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Page Title</label>
                                <input type="text" class="form-control" placeholder="Enter page title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" class="form-control" placeholder="page-url-slug">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Page Template</label>
                        <select class="form-control">
                            <option>Basic Page</option>
                            <option>Landing Page</option>
                            <option>Contact Page</option>
                            <option>Custom Template</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea class="form-control" rows="2" placeholder="Brief description for search engines..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Content</label>
                        <div id="editor" style="height: 300px;">
                            <p>Start writing your page content here...</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control">
                                    <option>Draft</option>
                                    <option>Published</option>
                                    <option>Scheduled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Publish Date</label>
                                <input type="datetime-local" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning">Save as Draft</button>
                <button type="button" class="btn btn-primary">Publish</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rich text editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Search functionality
    document.getElementById('pageSearch').addEventListener('keyup', function() {
        var value = this.value.toLowerCase();
        var rows = document.querySelectorAll('#pagesTable tbody tr');
        
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
});
</script>
@endpush

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.text-purple {
    color: #6f42c1 !important;
}
</style>
@endpush
