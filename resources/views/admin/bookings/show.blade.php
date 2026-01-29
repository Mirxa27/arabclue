@extends('layouts.admin')

@section('title', 'Booking Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Booking Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active">Booking #BK001</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    Actions
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#statusModal">
                        <i class="fas fa-edit"></i> Update Status
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#refundModal">
                        <i class="fas fa-undo"></i> Process Refund
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-times"></i> Cancel Booking
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Booking Information -->
        <div class="col-lg-8">
            <!-- Booking Overview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Overview</h6>
                    <span class="badge badge-success badge-lg">Confirmed</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold mb-3">Booking Information</h6>
                            <div class="mb-2">
                                <strong>Booking ID:</strong> #BK001
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong> <span class="badge badge-success">Confirmed</span>
                            </div>
                            <div class="mb-2">
                                <strong>Check-in Date:</strong> January 15, 2024
                            </div>
                            <div class="mb-2">
                                <strong>Check-out Date:</strong> January 20, 2024
                            </div>
                            <div class="mb-2">
                                <strong>Duration:</strong> 5 nights
                            </div>
                            <div class="mb-2">
                                <strong>Guests:</strong> 2 adults, 1 child
                            </div>
                            <div class="mb-2">
                                <strong>Booking Date:</strong> December 20, 2023
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold mb-3">Payment Information</h6>
                            <div class="mb-2">
                                <strong>Total Amount:</strong> $850.00
                            </div>
                            <div class="mb-2">
                                <strong>Payment Status:</strong> <span class="badge badge-success">Paid</span>
                            </div>
                            <div class="mb-2">
                                <strong>Payment Method:</strong> Credit Card (****1234)
                            </div>
                            <div class="mb-2">
                                <strong>Commission:</strong> $42.50 (5%)
                            </div>
                            <div class="mb-2">
                                <strong>Host Payout:</strong> $807.50
                            </div>
                            <div class="mb-2">
                                <strong>Currency:</strong> USD
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Property Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded" alt="Property">
                        </div>
                        <div class="col-md-8">
                            <h5 class="mb-2">Luxury Apartment in Downtown Dubai</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i> Downtown Dubai, Dubai, UAE
                            </p>
                            <p class="mb-3">A beautiful luxury apartment with stunning city views, modern amenities, and excellent location in the heart of Dubai.</p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-2">
                                        <strong>Property Type:</strong> Apartment
                                    </div>
                                    <div class="mb-2">
                                        <strong>Accommodates:</strong> 4 guests
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-2">
                                        <strong>Bedrooms:</strong> 2
                                    </div>
                                    <div class="mb-2">
                                        <strong>Bathrooms:</strong> 2
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">View Property</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content ml-3">
                                    <h6 class="mb-1">Booking Confirmed</h6>
                                    <p class="text-muted mb-1">Payment processed successfully and booking confirmed</p>
                                    <small class="text-muted">December 20, 2023 at 2:30 PM</small>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content ml-3">
                                    <h6 class="mb-1">Payment Received</h6>
                                    <p class="text-muted mb-1">Payment of $850.00 received via Credit Card</p>
                                    <small class="text-muted">December 20, 2023 at 2:25 PM</small>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content ml-3">
                                    <h6 class="mb-1">Booking Created</h6>
                                    <p class="text-muted mb-1">New booking request submitted by guest</p>
                                    <small class="text-muted">December 20, 2023 at 2:15 PM</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Guest Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Guest Information</h6>
                </div>
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/100x100" class="rounded-circle mb-3" width="100" height="100" alt="Guest">
                    <h6 class="mb-1">John Doe</h6>
                    <p class="text-muted mb-2">Premium Guest</p>
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span class="ml-1">4.9</span>
                    </div>
                    <div class="mb-3">
                        <div class="mb-2">
                            <strong>Email:</strong><br>
                            <a href="mailto:john.doe@example.com">john.doe@example.com</a>
                        </div>
                        <div class="mb-2">
                            <strong>Phone:</strong><br>
                            <a href="tel:+15551234567">+1 (555) 123-4567</a>
                        </div>
                        <div class="mb-2">
                            <strong>Member Since:</strong><br>
                            January 2023
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="mb-0">15</h6>
                            <small class="text-muted">Bookings</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">98%</h6>
                            <small class="text-muted">Response</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">4.9</h6>
                            <small class="text-muted">Rating</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Host Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Host Information</h6>
                </div>
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/80x80" class="rounded-circle mb-3" width="80" height="80" alt="Host">
                    <h6 class="mb-1">Sarah Johnson</h6>
                    <p class="text-muted mb-2">Superhost</p>
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span class="ml-1">4.8</span>
                    </div>
                    <div class="mb-3">
                        <div class="mb-2">
                            <strong>Email:</strong><br>
                            <a href="mailto:sarah.j@example.com">sarah.j@example.com</a>
                        </div>
                        <div class="mb-2">
                            <strong>Properties:</strong> 3
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
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#messageModal">
                            <i class="fas fa-envelope"></i> Send Message
                        </button>
                        <button class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#statusModal">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                        <button class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#refundModal">
                            <i class="fas fa-undo"></i> Process Refund
                        </button>
                        <button class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-download"></i> Download Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Current Status</label>
                        <input type="text" class="form-control" value="Confirmed" readonly>
                    </div>
                    <div class="form-group">
                        <label>New Status</label>
                        <select class="form-control">
                            <option>Confirmed</option>
                            <option>Checked In</option>
                            <option>Checked Out</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea class="form-control" rows="3" placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Refund</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Total Paid</label>
                        <input type="text" class="form-control" value="$850.00" readonly>
                    </div>
                    <div class="form-group">
                        <label>Refund Amount</label>
                        <input type="number" class="form-control" step="0.01" max="850" placeholder="Enter refund amount">
                    </div>
                    <div class="form-group">
                        <label>Refund Reason</label>
                        <select class="form-control">
                            <option>Guest Cancellation</option>
                            <option>Host Cancellation</option>
                            <option>Property Issues</option>
                            <option>Force Majeure</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" rows="3" placeholder="Add refund notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning">Process Refund</button>
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

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush
