@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">User Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">User Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <button class="btn btn-primary" data-toggle="modal" data-target="#editUserModal">
                <i class="fas fa-edit"></i> Edit User
            </button>
        </div>
    </div>

    <div class="row">
        <!-- User Information Card -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://via.placeholder.com/150x150?text=User" class="rounded-circle mb-3" width="150" height="150" alt="User Avatar">
                        <h5 class="mb-1">John Doe</h5>
                        <p class="text-muted">Premium User</p>
                        <span class="badge badge-success">Active</span>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="mb-0">15</h6>
                            <small class="text-muted">Bookings</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">3</h6>
                            <small class="text-muted">Properties</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">4.8</h6>
                            <small class="text-muted">Rating</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Email:</label>
                        <p class="mb-1">john.doe@example.com</p>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold">Phone:</label>
                        <p class="mb-1">+1 (555) 123-4567</p>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold">Address:</label>
                        <p class="mb-1">123 Main St, City, State 12345</p>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold">Joined:</label>
                        <p class="mb-1">January 15, 2023</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Activity and Details -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Spent</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$5,420</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Properties Hosted</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-home fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Average Rating</div>
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

            <!-- Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="bookings-tab" data-toggle="tab" href="#bookings" role="tab">Recent Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="properties-tab" data-toggle="tab" href="#properties" role="tab">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="activity-tab" data-toggle="tab" href="#activity" role="tab">Activity Log</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="userTabsContent">
                        <!-- Recent Bookings -->
                        <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Property</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#BK001</td>
                                            <td>Luxury Apartment Dubai</td>
                                            <td>2024-01-15</td>
                                            <td>2024-01-20</td>
                                            <td><span class="badge badge-success">Completed</span></td>
                                            <td>$850</td>
                                        </tr>
                                        <tr>
                                            <td>#BK002</td>
                                            <td>Beach Villa Maldives</td>
                                            <td>2024-02-10</td>
                                            <td>2024-02-17</td>
                                            <td><span class="badge badge-info">Confirmed</span></td>
                                            <td>$1,200</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Properties -->
                        <div class="tab-pane fade" id="properties" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Property">
                                        <div class="card-body">
                                            <h6 class="card-title">Downtown Apartment</h6>
                                            <p class="card-text text-muted">Dubai, UAE</p>
                                            <div class="d-flex justify-content-between">
                                                <span class="badge badge-success">Active</span>
                                                <span>$120/night</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews -->
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            <div class="mb-3">
                                <div class="d-flex">
                                    <img src="https://via.placeholder.com/50x50" class="rounded-circle mr-3" alt="Reviewer">
                                    <div>
                                        <h6 class="mb-1">Great host and beautiful property!</h6>
                                        <div class="text-warning mb-1">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <p class="text-muted mb-1">John was an excellent host. The property was exactly as described...</p>
                                        <small class="text-muted">January 25, 2024</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <div class="timeline">
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content ml-3">
                                            <h6 class="mb-1">Profile Updated</h6>
                                            <p class="text-muted mb-1">User updated their profile information</p>
                                            <small class="text-muted">2 hours ago</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content ml-3">
                                            <h6 class="mb-1">New Booking</h6>
                                            <p class="text-muted mb-1">Made a booking for Beach Villa Maldives</p>
                                            <small class="text-muted">1 day ago</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" value="John">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" value="Doe">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="john.doe@example.com">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" value="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control">
                            <option>Active</option>
                            <option>Suspended</option>
                            <option>Banned</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control">
                            <option>Guest</option>
                            <option>Host</option>
                            <option>Premium User</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 4px;
}
</style>
@endpush
