@extends('layouts.admin')

@section('title', 'Booking Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Booking Reports</h1>
            <p class="mb-0 text-muted">Comprehensive booking analytics and performance metrics</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportBookingReport()">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
            <button type="button" class="btn btn-outline-info" onclick="emailReport()">
                <i class="fas fa-envelope me-2"></i>Email Report
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterByStatus('all')">All Bookings</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterByStatus('confirmed')">Confirmed</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterByStatus('pending')">Pending</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterByStatus('cancelled')">Cancelled</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="advancedFilter()">Advanced Filter</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Booking Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,847</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 18.2% vs last month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Confirmed Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,456</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 86.3% confirmation rate
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">248</div>
                            <div class="text-xs text-warning">
                                <i class="fas fa-clock"></i> 8.7% pending rate
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">143</div>
                            <div class="text-xs text-danger">
                                <i class="fas fa-arrow-down"></i> 5.0% cancellation rate
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Analytics Charts -->
    <div class="row mb-4">
        <!-- Booking Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Trends</h6>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="bookingChart" id="bookingDaily" checked>
             <label class="btn btn-outline-primary" for="bookingDaily">Daily</label>
                            
                            <input type="radio" class="btn-check" name="bookingChart" id="bookingWeekly">
                            <label class="btn btn-outline-primary" for="bookingWeekly">Weekly</label>
                            
                            <input type="radio" class="btn-check" name="bookingChart" id="bookingMonthly">
                            <label class="btn btn-outline-primary" for="bookingMonthly">Monthly</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="bookingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Status Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="bookingStatusChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-success me-2"></div>
                                <span class="small">Confirmed</span>
                            </div>
                            <span class="small font-weight-bold">86.3%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-warning me-2"></div>
                                <span class="small">Pending</span>
                            </div>
                            <span class="small font-weight-bold">8.7%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-danger me-2"></div>
                                <span class="small">Cancelled</span>
                            </div>
                            <span class="small font-weight-bold">5.0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="row mb-4">
        <!-- Booking Performance by Property Type -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance by Property Type</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Property Type</th>
                                    <th>Bookings</th>
                                    <th>Avg. Stay</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <span>Apartments</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">1,245</td>
                                    <td>3.2 days</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-1">4.6</span>
                                            <div class="text-warning">
                                                <i class="fas fa-star fa-sm"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-home text-success me-2"></i>
                                            <span>Villas</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">687</td>
                                    <td>5.8 days</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-1">4.8</span>
                                            <div class="text-warning">
                                                <i class="fas fa-star fa-sm"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hotel text-info me-2"></i>
                                            <span>Hotels</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">523</td>
                                    <td>2.1 days</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-1">4.4</span>
                                            <div class="text-warning">
                                                <i class="fas fa-star fa-sm"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-warehouse text-secondary me-2"></i>
                                            <span>Studios</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">392</td>
                                    <td>4.5 days</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-1">4.3</span>
                                            <div class="text-warning">
                                                <i class="fas fa-star fa-sm"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Source Analysis -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Sources</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="bookingSourceChart"></canvas>
                    </div>
                    <div class="mt-3 row">
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Direct Website</span>
                                <span class="small font-weight-bold">45.2%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Mobile App</span>
                                <span class="small font-weight-bold">28.7%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small">Third Party</span>
                                <span class="small font-weight-bold">18.3%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Social Media</span>
                                <span class="small font-weight-bold">5.1%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Email Campaign</span>
                                <span class="small font-weight-bold">2.2%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small">Other</span>
                                <span class="small font-weight-bold">0.5%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Analytics -->
    <div class="row mb-4">
        <!-- Customer Demographics -->
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Demographics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="small font-weight-bold">Age Groups</h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">25-34</span>
                                <span class="small">38%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 38%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">35-44</span>
                                <span class="small">28%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 28%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">18-24</span>
                                <span class="small">18%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: 18%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">45-54</span>
                                <span class="small">12%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 12%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between">
                                <span class="small">55+</span>
                                <span class="small">4%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-secondary" style="width: 4%"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="small font-weight-bold">Guest Countries</h6>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">🇦🇪 UAE</span>
                            <span class="small">42%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">🇺🇸 USA</span>
                            <span class="small">18%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">🇬🇧 UK</span>
                            <span class="small">12%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">🇩🇪 Germany</span>
                            <span class="small">8%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small">🌍 Others</span>
                            <span class="small">20%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Patterns -->
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Patterns</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="small font-weight-bold">Advance Booking</h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">Same Day</span>
                                <span class="small">8%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: 8%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">1-7 Days</span>
                                <span class="small">22%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 22%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">1-4 Weeks</span>
                                <span class="small">35%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 35%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">1-3 Months</span>
                                <span class="small">28%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 28%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between">
                                <span class="small">3+ Months</span>
                                <span class="small">7%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: 7%"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="small font-weight-bold">Stay Duration</h6>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">1 Night</span>
                            <span class="small">15%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">2-3 Nights</span>
                            <span class="small">42%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">4-7 Nights</span>
                            <span class="small">28%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">1-2 Weeks</span>
                            <span class="small">12%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small">2+ Weeks</span>
                            <span class="small">3%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="col-xl-4 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="bg-light p-3 rounded">
                                <h4 class="text-primary mb-1">3.4</h4>
                                <p class="mb-0 small text-muted">Avg. Stay Duration</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-light p-3 rounded">
                                <h4 class="text-success mb-1">4.6</h4>
                                <p class="mb-0 small text-muted">Avg. Rating</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-light p-3 rounded">
                                <h4 class="text-info mb-1">18.5</h4>
                                <p class="mb-0 small text-muted">Days Advance</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-light p-3 rounded">
                                <h4 class="text-warning mb-1">72%</h4>
                                <p class="mb-0 small text-muted">Repeat Guests</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div>
                        <h6 class="small font-weight-bold mb-3">Seasonal Trends</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Peak Season (Dec-Mar)</span>
                            <span class="small font-weight-bold text-success">+45%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">High Season (Nov, Apr)</span>
                            <span class="small font-weight-bold text-info">+25%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">Regular Season (May-Oct)</span>
                            <span class="small font-weight-bold text-secondary">Baseline</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small">Low Season (Aug-Sep)</span>
                            <span class="small font-weight-bold text-warning">-20%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Booking Trend Chart
const bookingTrendCtx = document.getElementById('bookingTrendChart').getContext('2d');
const bookingTrendChart = new Chart(bookingTrendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Total Bookings',
            data: [180, 210, 250, 220, 190, 160, 140, 130, 170, 200, 280, 320],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }, {
            label: 'Confirmed Bookings',
            data: [155, 182, 215, 190, 164, 138, 121, 112, 147, 173, 242, 276],
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Booking Status Chart
const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
const bookingStatusChart = new Chart(bookingStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Confirmed', 'Pending', 'Cancelled'],
        datasets: [{
            data: [86.3, 8.7, 5.0],
            backgroundColor: [
                '#1cc88a',
                '#f6c23e',
                '#e74a3b'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Booking Source Chart
const bookingSourceCtx = document.getElementById('bookingSourceChart').getContext('2d');
const bookingSourceChart = new Chart(bookingSourceCtx, {
    type: 'bar',
    data: {
        labels: ['Direct', 'Mobile App', 'Third Party', 'Social Media', 'Email', 'Other'],
        datasets: [{
            label: 'Booking %',
            data: [45.2, 28.7, 18.3, 5.1, 2.2, 0.5],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b',
                '#858796'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Booking report functions
function exportBookingReport() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
    btn.disabled = true;
    
    setTimeout(() => {
        showNotification('Booking report exported successfully!', 'success');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function emailReport() {
    showNotification('Report emailed successfully!', 'success');
}

function filterByStatus(status) {
    console.log('Filtering by status:', status);
    showNotification(`Filtered by ${status} bookings`, 'info');
}

function advancedFilter() {
    showNotification('Advanced filter feature coming soon!', 'info');
}

// Utility function for notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Legend color styles
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
