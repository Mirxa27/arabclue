@extends('layouts.admin')

@section('title', 'Revenue Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Revenue Reports</h1>
            <p class="mb-0 text-muted">Detailed financial performance and revenue analytics</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportReport()">
                <i class="fas fa-download me-2"></i>Export PDF
            </button>
            <button type="button" class="btn btn-outline-info" onclick="scheduleReport()">
                <i class="fas fa-clock me-2"></i>Schedule Report
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-2"></i>Date Range
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="setDateRange('today')">Today</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setDateRange('week')">This Week</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setDateRange('month')">This Month</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setDateRange('quarter')">This Quarter</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setDateRange('year')">This Year</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="customDateRange()">Custom Range</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$42,857</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 12.5% vs last period
                            </div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Net Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$38,423</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 8.2% vs last period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Daily Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$1,238</div>
                            <div class="text-xs text-info">
                                <i class="fas fa-equals"></i> 3.1% vs last period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Commission Earned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$6,428</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> 15.7% vs last period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Charts -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Trend</h6>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="revenueChart" id="daily" checked>
                            <label class="btn btn-outline-primary" for="daily">Daily</label>
                            
                            <input type="radio" class="btn-check" name="revenueChart" id="weekly">
                            <label class="btn btn-outline-primary" for="weekly">Weekly</label>
                            
                            <input type="radio" class="btn-check" name="revenueChart" id="monthly">
                            <label class="btn btn-outline-primary" for="monthly">Monthly</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Breakdown</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="revenueBreakdownChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-primary me-2"></div>
                                <span class="small">Bookings</span>
                            </div>
                            <span class="small font-weight-bold">89.3%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-success me-2"></div>
                                <span class="small">Extra Services</span>
                            </div>
                            <span class="small font-weight-bold">7.2%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="legend-color bg-info me-2"></div>
                                <span class="small">Other</span>
                            </div>
                            <span class="small font-weight-bold">3.5%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Analysis -->
    <div class="row mb-4">
        <!-- Top Performing Properties -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Revenue Properties</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Revenue</th>
                                    <th>Bookings</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://picsum.photos/40/30?random=1" class="rounded me-2" alt="Property">
                                            <div>
                                                <div class="font-weight-bold">Luxury Beachfront Villa</div>
                                                <small class="text-muted">Dubai Marina</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">$8,450</td>
                                    <td>12</td>
                                    <td><span class="text-success">+15.2%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://picsum.photos/40/30?random=2" class="rounded me-2" alt="Property">
                                            <div>
                                                <div class="font-weight-bold">Modern Downtown Apartment</div>
                                                <small class="text-muted">Business Bay</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">$6,780</td>
                                    <td>18</td>
                                    <td><span class="text-success">+8.7%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://picsum.photos/40/30?random=3" class="rounded me-2" alt="Property">
                                            <div>
                                                <div class="font-weight-bold">Family Resort Suite</div>
                                                <small class="text-muted">Palm Jumeirah</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">$5,920</td>
                                    <td>15</td>
                                    <td><span class="text-success">+12.3%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://picsum.photos/40/30?random=4" class="rounded me-2" alt="Property">
                                            <div>
                                                <div class="font-weight-bold">Cozy Studio Apartment</div>
                                                <small class="text-muted">JLT</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">$4,350</td>
                                    <td>22</td>
                                    <td><span class="text-danger">-2.1%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://picsum.photos/40/30?random=5" class="rounded me-2" alt="Property">
                                            <div>
                                                <div class="font-weight-bold">Penthouse with City View</div>
                                                <small class="text-muted">DIFC</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">$3,890</td>
                                    <td>8</td>
                                    <td><span class="text-success">+24.5%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Location -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue by Location</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Dubai Marina</span>
                            <span class="font-weight-bold">$12,450</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 85%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Downtown Dubai</span>
                            <span class="font-weight-bold">$9,830</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 67%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Palm Jumeirah</span>
                            <span class="font-weight-bold">$8,750</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: 59%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Business Bay</span>
                            <span class="font-weight-bold">$6,420</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: 44%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>JLT</span>
                            <span class="font-weight-bold">$5,407</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-secondary" style="width: 37%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Metrics & KPIs</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="customizeMetrics()">Customize View</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportMetrics()">Export Data</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="compareMetrics()">Compare Periods</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h4 class="text-primary mb-1">15.2%</h4>
                                <p class="mb-0 small text-muted">Revenue Growth Rate</p>
                                <small class="text-success">vs previous period</small>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h4 class="text-success mb-1">$376</h4>
                                <p class="mb-0 small text-muted">Average Revenue Per Booking</p>
                                <small class="text-success">+8.3% increase</small>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h4 class="text-info mb-1">72.4%</h4>
                                <p class="mb-0 small text-muted">Revenue Conversion Rate</p>
                                <small class="text-info">stable</small>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h4 class="text-warning mb-1">4.8</h4>
                                <p class="mb-0 small text-muted">Days Average Payment Time</p>
                                <small class="text-success">-0.2 days improvement</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="font-weight-bold mb-3">Revenue Forecast</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Next 7 Days</span>
                                <span class="font-weight-bold text-primary">$8,650</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Next 30 Days</span>
                                <span class="font-weight-bold text-success">$35,420</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Next Quarter</span>
                                <span class="font-weight-bold text-info">$128,750</span>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h6 class="font-weight-bold mb-3">Payment Methods</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Credit Cards</span>
                                <span class="font-weight-bold">68.5%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Bank Transfers</span>
                                <span class="font-weight-bold">18.2%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Digital Wallets</span>
                                <span class="font-weight-bold">10.8%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Cash</span>
                                <span class="font-weight-bold">2.5%</span>
                            </div>
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
// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 15000, 18000, 16000, 22000, 25000, 28000, 24000, 30000, 32000, 35000, 42000],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }, {
            label: 'Net Revenue',
            data: [10800, 13500, 16200, 14400, 19800, 22500, 25200, 21600, 27000, 28800, 31500, 37800],
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

// Revenue Breakdown Chart
const breakdownCtx = document.getElementById('revenueBreakdownChart').getContext('2d');
const breakdownChart = new Chart(breakdownCtx, {
    type: 'doughnut',
    data: {
        labels: ['Bookings', 'Extra Services', 'Other'],
        datasets: [{
            data: [89.3, 7.2, 3.5],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc'
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

// Revenue report functions
function exportReport() {
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    btn.disabled = true;
    
    // Simulate export
    setTimeout(() => {
        showNotification('Revenue report exported successfully!', 'success');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function scheduleReport() {
    // Implementation for scheduling recurring reports
    showNotification('Report scheduling feature coming soon!', 'info');
}

function setDateRange(range) {
    console.log('Setting date range to:', range);
    // Update charts and data based on selected range
    showNotification(`Date range updated to ${range}`, 'info');
}

function customDateRange() {
    // Implementation for custom date range picker
    showNotification('Custom date range picker coming soon!', 'info');
}

function customizeMetrics() {
    showNotification('Metrics customization feature coming soon!', 'info');
}

function exportMetrics() {
    showNotification('Metrics exported successfully!', 'success');
}

function compareMetrics() {
    showNotification('Period comparison feature coming soon!', 'info');
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
