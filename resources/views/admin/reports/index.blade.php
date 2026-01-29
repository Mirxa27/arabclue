@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark font-weight-bold mb-1">Reports & Analytics</h2>
                    <p class="text-muted mb-0">Comprehensive business insights and performance metrics</p>
                </div>
                <div>
                    <button class="btn btn-primary me-2" onclick="exportReport()">
                        <i class="fas fa-download me-2"></i>Export Report
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customReportModal">
                        <i class="fas fa-chart-line me-2"></i>Custom Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form class="row align-items-end">
                        <div class="col-md-3">
                            <label for="date-from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date-from" value="2024-01-01">
                        </div>
                        <div class="col-md-3">
                            <label for="date-to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date-to" value="2024-01-31">
                        </div>
                        <div class="col-md-3">
                            <label for="report-type" class="form-label">Report Type</label>
                            <select class="form-control" id="report-type">
                                <option value="overview" selected>Overview</option>
                                <option value="revenue">Revenue Analysis</option>
                                <option value="bookings">Booking Performance</option>
                                <option value="properties">Property Performance</option>
                                <option value="users">User Analytics</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-block" onclick="generateReport()">
                                <i class="fas fa-chart-bar me-2"></i>Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-revenue">$0</div>
                            <div class="text-xs text-success mt-1" id="revenue-change">+0% from last period</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-bookings">0</div>
                            <div class="text-xs text-success mt-1" id="bookings-change">+0% from last period</div>
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
                                Occupancy Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="occupancy-rate">0%</div>
                            <div class="text-xs text-info mt-1" id="occupancy-change">+0% from last period</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Avg. Daily Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avg-daily-rate">$0</div>
                            <div class="text-xs text-warning mt-1" id="adr-change">+0% from last period</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Monthly
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateChart('daily')">Daily</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('weekly')">Weekly</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChart('monthly')">Monthly</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Status Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Booking Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Performance Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top Performing Properties</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="propertyPerformanceTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Total Bookings</th>
                            <th>Revenue</th>
                            <th>Occupancy Rate</th>
                            <th>Avg. Rating</th>
                            <th>Total Nights</th>
                        </tr>
                    </thead>
                    <tbody id="property-performance-body">
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

    <!-- Additional Analytics -->
    <div class="row">
        <!-- User Registration Trend -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Registration Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="userRegistrationChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Geographic Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bookings by Location</h6>
                </div>
                <div class="card-body">
                    <canvas id="geographicChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Custom Report</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customReportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report-name">Report Name</label>
                                <input type="text" class="form-control" id="report-name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report-frequency">Frequency</label>
                                <select class="form-control" id="report-frequency">
                                    <option value="once">One-time</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Metrics to Include</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-revenue" checked>
                                    <label class="custom-control-label" for="metric-revenue">Revenue</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-bookings" checked>
                                    <label class="custom-control-label" for="metric-bookings">Bookings</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-occupancy">
                                    <label class="custom-control-label" for="metric-occupancy">Occupancy Rate</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-users">
                                    <label class="custom-control-label" for="metric-users">User Analytics</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-properties">
                                    <label class="custom-control-label" for="metric-properties">Property Performance</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="metric-reviews">
                                    <label class="custom-control-label" for="metric-reviews">Reviews & Ratings</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="report-email">Email Recipients</label>
                        <input type="email" class="form-control" id="report-email" placeholder="admin@habibistay.com">
                        <small class="form-text text-muted">Comma-separated email addresses</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCustomReport()">Create Report</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueChart, bookingStatusChart, userRegistrationChart, geographicChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadReportData();
});

function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [5000, 7500, 6200, 8900, 9500, 11200],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Booking Status Chart
    const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
    bookingStatusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Cancelled', 'Completed'],
            datasets: [{
                data: [45, 12, 8, 35],
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // User Registration Chart
    const userCtx = document.getElementById('userRegistrationChart').getContext('2d');
    userRegistrationChart = new Chart(userCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [120, 95, 180, 150, 220, 190],
                backgroundColor: '#36b9cc'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Geographic Chart
    const geoCtx = document.getElementById('geographicChart').getContext('2d');
    geographicChart = new Chart(geoCtx, {
        type: 'horizontalBar',
        data: {
            labels: ['New York', 'Los Angeles', 'Chicago', 'Miami', 'Boston'],
            datasets: [{
                label: 'Bookings',
                data: [45, 38, 29, 22, 16],
                backgroundColor: '#5a5c69'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadReportData() {
    // Simulate loading report data
    setTimeout(() => {
        document.getElementById('total-revenue').textContent = '$48,250';
        document.getElementById('revenue-change').textContent = '+12.5% from last period';
        
        document.getElementById('total-bookings').textContent = '156';
        document.getElementById('bookings-change').textContent = '+8.3% from last period';
        
        document.getElementById('occupancy-rate').textContent = '72%';
        document.getElementById('occupancy-change').textContent = '+5.2% from last period';
        
        document.getElementById('avg-daily-rate').textContent = '$185';
        document.getElementById('adr-change').textContent = '+3.8% from last period';
        
        loadPropertyPerformance();
    }, 1000);
}

function loadPropertyPerformance() {
    const tbody = document.getElementById('property-performance-body');
    
    setTimeout(() => {
        const sampleData = [
            {
                name: 'Luxury Downtown Apartment',
                bookings: 24,
                revenue: 12500,
                occupancy: 85,
                rating: 4.8,
                nights: 180
            },
            {
                name: 'Cozy Beach House',
                bookings: 18,
                revenue: 9200,
                occupancy: 72,
                rating: 4.6,
                nights: 144
            },
            {
                name: 'Modern City Loft',
                bookings: 21,
                revenue: 8900,
                occupancy: 68,
                rating: 4.7,
                nights: 168
            }
        ];
        
        tbody.innerHTML = sampleData.map(property => `
            <tr>
                <td>${property.name}</td>
                <td>${property.bookings}</td>
                <td>$${property.revenue.toLocaleString()}</td>
                <td>${property.occupancy}%</td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="me-2">${property.rating}</span>
                        <div class="text-warning">
                            ${'★'.repeat(Math.floor(property.rating))}${'☆'.repeat(5 - Math.floor(property.rating))}
                        </div>
                    </div>
                </td>
                <td>${property.nights}</td>
            </tr>
        `).join('');
    }, 1500);
}

function generateReport() {
    const reportType = document.getElementById('report-type').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    // Simulate report generation
    alert(`Generating ${reportType} report from ${dateFrom} to ${dateTo}...`);
    loadReportData();
}

function updateChart(period) {
    // Simulate updating chart data
    alert(`Updating chart to show ${period} data...`);
}

function exportReport() {
    alert('Exporting report as PDF...');
}

function createCustomReport() {
    const reportName = document.getElementById('report-name').value;
    if (!reportName) {
        alert('Please enter a report name');
        return;
    }
    
    setTimeout(() => {
        alert('Custom report created successfully!');
        document.getElementById('customReportModal').querySelector('[data-bs-dismiss="modal"]').click();
        document.getElementById('customReportForm').reset();
    }, 1000);
}
</script>
@endsection
