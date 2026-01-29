@extends('layouts.admin')

@section('title', 'Homepage Sliders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Homepage Sliders</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.content.index') }}">Content</a></li>
                    <li class="breadcrumb-item active">Sliders</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createSliderModal">
                <i class="fas fa-plus"></i> Add New Slide
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Slides</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Slides</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg. Click Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12.5%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mouse-pointer fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Views</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">45.2K</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Slider Preview -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Live Preview</h6>
        </div>
        <div class="card-body p-0">
            <div id="sliderPreview" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#sliderPreview" data-slide-to="0" class="active"></li>
                    <li data-target="#sliderPreview" data-slide-to="1"></li>
                    <li data-target="#sliderPreview" data-slide-to="2"></li>
                    <li data-target="#sliderPreview" data-slide-to="3"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="slide-content" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 400px; position: relative;">
                            <div class="slide-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-white">
                                    <h2 class="display-4 font-weight-bold mb-3">Welcome to HabibiStay</h2>
                                    <p class="lead mb-4">Discover amazing places to stay around the world</p>
                                    <a href="#" class="btn btn-light btn-lg">Start Exploring</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="slide-content" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); height: 400px; position: relative;">
                            <div class="slide-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-white">
                                    <h2 class="display-4 font-weight-bold mb-3">Luxury Accommodations</h2>
                                    <p class="lead mb-4">Experience premium comfort in stunning locations</p>
                                    <a href="#" class="btn btn-light btn-lg">View Properties</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="slide-content" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); height: 400px; position: relative;">
                            <div class="slide-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-white">
                                    <h2 class="display-4 font-weight-bold mb-3">Book with Confidence</h2>
                                    <p class="lead mb-4">Secure booking with 24/7 customer support</p>
                                    <a href="#" class="btn btn-light btn-lg">Learn More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="slide-content" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); height: 400px; position: relative;">
                            <div class="slide-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-white">
                                    <h2 class="display-4 font-weight-bold mb-3">Host Your Property</h2>
                                    <p class="lead mb-4">Earn extra income by hosting guests</p>
                                    <a href="#" class="btn btn-light btn-lg">Become a Host</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a class="carousel-control-prev" href="#sliderPreview" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#sliderPreview" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Slides Management -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">Manage Slides</h6>
                </div>
                <div class="col-auto">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="autoSlide" checked>
                        <label class="custom-control-label" for="autoSlide">Auto-slide</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="slidesTable">
                    <thead>
                        <tr>
                            <th width="50">Order</th>
                            <th>Slide</th>
                            <th>Title</th>
                            <th>Call to Action</th>
                            <th>Status</th>
                            <th>Stats</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortableSlides">
                        <tr data-id="1">
                            <td class="text-center">
                                <i class="fas fa-grip-vertical text-muted handle" style="cursor: move;"></i>
                                <span class="ml-2">1</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="slide-thumbnail mr-3" style="width: 80px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px; position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px; text-align: center;">
                                            <i class="fas fa-home"></i><br>Welcome
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Welcome Slide</div>
                                        <small class="text-muted">Main landing slide</small>
                                    </div>
                                </div>
                            </td>
                            <td>Welcome to HabibiStay</td>
                            <td>
                                <span class="badge badge-primary">Start Exploring</span>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="slide1" checked>
                                    <label class="custom-control-label" for="slide1">Active</label>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Views: 15.2K<br>
                                    Clicks: 1.8K
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit" data-toggle="modal" data-target="#editSliderModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-id="2">
                            <td class="text-center">
                                <i class="fas fa-grip-vertical text-muted handle" style="cursor: move;"></i>
                                <span class="ml-2">2</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="slide-thumbnail mr-3" style="width: 80px; height: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 4px; position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px; text-align: center;">
                                            <i class="fas fa-star"></i><br>Luxury
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Luxury Slide</div>
                                        <small class="text-muted">Premium properties</small>
                                    </div>
                                </div>
                            </td>
                            <td>Luxury Accommodations</td>
                            <td>
                                <span class="badge badge-info">View Properties</span>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="slide2" checked>
                                    <label class="custom-control-label" for="slide2">Active</label>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Views: 12.1K<br>
                                    Clicks: 2.1K
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-id="3">
                            <td class="text-center">
                                <i class="fas fa-grip-vertical text-muted handle" style="cursor: move;"></i>
                                <span class="ml-2">3</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="slide-thumbnail mr-3" style="width: 80px; height: 50px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 4px; position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px; text-align: center;">
                                            <i class="fas fa-shield-alt"></i><br>Trust
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Trust Slide</div>
                                        <small class="text-muted">Security & support</small>
                                    </div>
                                </div>
                            </td>
                            <td>Book with Confidence</td>
                            <td>
                                <span class="badge badge-success">Learn More</span>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="slide3" checked>
                                    <label class="custom-control-label" for="slide3">Active</label>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Views: 9.8K<br>
                                    Clicks: 945
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-id="4">
                            <td class="text-center">
                                <i class="fas fa-grip-vertical text-muted handle" style="cursor: move;"></i>
                                <span class="ml-2">4</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="slide-thumbnail mr-3" style="width: 80px; height: 50px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 4px; position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 10px; text-align: center;">
                                            <i class="fas fa-plus"></i><br>Host
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Host Slide</div>
                                        <small class="text-muted">Become a host</small>
                                    </div>
                                </div>
                            </td>
                            <td>Host Your Property</td>
                            <td>
                                <span class="badge badge-warning">Become a Host</span>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="slide4" checked>
                                    <label class="custom-control-label" for="slide4">Active</label>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Views: 7.5K<br>
                                    Clicks: 892
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Slider Modal -->
<div class="modal fade" id="createSliderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Slide</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Slide Title</label>
                                <input type="text" class="form-control" placeholder="Enter slide title">
                            </div>
                            
                            <div class="form-group">
                                <label>Subtitle/Description</label>
                                <textarea class="form-control" rows="2" placeholder="Enter slide description"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Button Text</label>
                                        <input type="text" class="form-control" placeholder="e.g., Learn More">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Button Link</label>
                                        <input type="url" class="form-control" placeholder="https://">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Background Type</label>
                                <select class="form-control" id="backgroundType">
                                    <option value="gradient">Gradient</option>
                                    <option value="image">Image</option>
                                    <option value="color">Solid Color</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="gradientOptions">
                                <label>Gradient Colors</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="color" class="form-control" value="#667eea">
                                    </div>
                                    <div class="col-6">
                                        <input type="color" class="form-control" value="#764ba2">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group" id="imageOptions" style="display: none;">
                                <label>Background Image</label>
                                <input type="file" class="form-control-file" accept="image/*">
                            </div>
                            
                            <div class="form-group">
                                <label>Text Alignment</label>
                                <select class="form-control">
                                    <option>Center</option>
                                    <option>Left</option>
                                    <option>Right</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" class="form-control" value="5" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control">
                                    <option>Active</option>
                                    <option>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Slide</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable for slides
    new Sortable(document.getElementById('sortableSlides'), {
        handle: '.handle',
        animation: 150,
        onEnd: function(evt) {
            // Update order numbers
            var rows = document.querySelectorAll('#sortableSlides tr');
            rows.forEach(function(row, index) {
                row.querySelector('td span').textContent = index + 1;
            });
            
            // Here you would send AJAX request to update order in database
            console.log('Slide order updated');
        }
    });

    // Background type selector
    document.getElementById('backgroundType').addEventListener('change', function() {
        var gradientOptions = document.getElementById('gradientOptions');
        var imageOptions = document.getElementById('imageOptions');
        
        if (this.value === 'gradient') {
            gradientOptions.style.display = 'block';
            imageOptions.style.display = 'none';
        } else if (this.value === 'image') {
            gradientOptions.style.display = 'none';
            imageOptions.style.display = 'block';
        } else {
            gradientOptions.style.display = 'none';
            imageOptions.style.display = 'none';
        }
    });
});
</script>
@endpush
