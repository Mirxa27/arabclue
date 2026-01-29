@extends('layouts.admin')

@section('title', 'Property Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
                            <li class="breadcrumb-item active">Property Details</li>
                        </ol>
                    </nav>
                    <h2 class="text-dark font-weight-bold mb-1">Luxury Downtown Apartment</h2>
                    <p class="text-muted mb-0">View and manage property details</p>
                </div>
                <div>
                    <button class="btn btn-warning me-2" onclick="editProperty()">
                        <i class="fas fa-edit me-2"></i>Edit Property
                    </button>
                    <button class="btn btn-danger" onclick="deleteProperty()">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge badge-success">Active</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Total Bookings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                                Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$12,500</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Avg. Rating
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4.8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Property Details -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Property Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> Luxury Downtown Apartment</p>
                            <p><strong>Type:</strong> Apartment</p>
                            <p><strong>City:</strong> New York</p>
                            <p><strong>Address:</strong> 123 Main Street, Downtown</p>
                            <p><strong>Price per Night:</strong> $185</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bedrooms:</strong> 2</p>
                            <p><strong>Bathrooms:</strong> 2</p>
                            <p><strong>Max Guests:</strong> 4</p>
                            <p><strong>Area:</strong> 1200 sq ft</p>
                            <p><strong>Created:</strong> January 15, 2024</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p><strong>Description:</strong></p>
                        <p class="text-muted">Experience luxury living in the heart of downtown with this stunning 2-bedroom apartment. Featuring modern amenities, spectacular city views, and premium finishes throughout.</p>
                    </div>
                </div>
            </div>

            <!-- Images Gallery -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Property Images</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded" alt="Property Image">
                        </div>
                        <div class="col-md-4 mb-3">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded" alt="Property Image">
                        </div>
                        <div class="col-md-4 mb-3">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded" alt="Property Image">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Smith</td>
                                    <td>2024-01-20</td>
                                    <td>2024-01-23</td>
                                    <td><span class="badge badge-success">Confirmed</span></td>
                                    <td>$555</td>
                                </tr>
                                <tr>
                                    <td>Sarah Johnson</td>
                                    <td>2024-01-15</td>
                                    <td>2024-01-18</td>
                                    <td><span class="badge badge-info">Completed</span></td>
                                    <td>$555</td>
                                </tr>
                                <tr>
                                    <td>Mike Wilson</td>
                                    <td>2024-01-10</td>
                                    <td>2024-01-12</td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                    <td>$370</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Host Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Host Information</h6>
                </div>
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/100x100" class="rounded-circle mb-3" alt="Host Avatar">
                    <h5>Ahmed Hassan</h5>
                    <p class="text-muted">Host since 2020</p>
                    <p><strong>Rating:</strong> 4.9 (156 reviews)</p>
                    <button class="btn btn-primary btn-sm" onclick="viewHost()">View Host Profile</button>
                </div>
            </div>

            <!-- Amenities -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Amenities</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-wifi text-primary"></i> WiFi</li>
                                <li><i class="fas fa-car text-primary"></i> Parking</li>
                                <li><i class="fas fa-swimming-pool text-primary"></i> Pool</li>
                                <li><i class="fas fa-dumbbell text-primary"></i> Gym</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-wind text-primary"></i> AC</li>
                                <li><i class="fas fa-utensils text-primary"></i> Kitchen</li>
                                <li><i class="fas fa-tv text-primary"></i> TV</li>
                                <li><i class="fas fa-concierge-bell text-primary"></i> Room Service</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-success btn-block mb-2" onclick="toggleStatus()">
                        <i class="fas fa-toggle-on me-2"></i>Toggle Status
                    </button>
                    <button class="btn btn-info btn-block mb-2" onclick="viewCalendar()">
                        <i class="fas fa-calendar me-2"></i>View Calendar
                    </button>
                    <button class="btn btn-warning btn-block mb-2" onclick="managePrice()">
                        <i class="fas fa-dollar-sign me-2"></i>Manage Pricing
                    </button>
                    <button class="btn btn-secondary btn-block" onclick="generateReport()">
                        <i class="fas fa-chart-bar me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editProperty() {
    alert('Edit property functionality would be implemented here');
}

function deleteProperty() {
    if (confirm('Are you sure you want to delete this property?')) {
        alert('Property deletion functionality would be implemented here');
    }
}

function viewHost() {
    alert('View host profile functionality would be implemented here');
}

function toggleStatus() {
    alert('Toggle property status functionality would be implemented here');
}

function viewCalendar() {
    alert('View calendar functionality would be implemented here');
}

function managePrice() {
    alert('Manage pricing functionality would be implemented here');
}

function generateReport() {
    alert('Generate property report functionality would be implemented here');
}
</script>
@endsection
