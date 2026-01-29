@extends('layouts.admin')

@section('title', 'Currency Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Currency Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
                    <li class="breadcrumb-item active">Currencies</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-info" id="updateRatesBtn">
                <i class="fas fa-sync-alt"></i> Update Exchange Rates
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addCurrencyModal">
                <i class="fas fa-plus"></i> Add Currency
            </button>
        </div>
    </div>

    <!-- Currency Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Supported Currencies</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Currencies</div>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Base Currency</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">USD</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Last Updated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2h ago</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Configuration -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">Currency Configuration</h6>
                </div>
                <div class="col-auto">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle"></i> Rates updated every 4 hours
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="currenciesTable">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Code</th>
                            <th>Symbol</th>
                            <th>Exchange Rate</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/us.svg') center/cover;"></div>
                                    <span class="font-weight-bold">US Dollar</span>
                                </div>
                            </td>
                            <td><code>USD</code></td>
                            <td class="font-weight-bold">$</td>
                            <td>1.0000 <small class="text-muted">(Base)</small></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultUSD" name="defaultCurrency" checked>
                                    <label class="custom-control-label" for="defaultUSD"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="USD">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/eu.svg') center/cover;"></div>
                                    <span class="font-weight-bold">Euro</span>
                                </div>
                            </td>
                            <td><code>EUR</code></td>
                            <td class="font-weight-bold">€</td>
                            <td>0.8521 <small class="text-success">+0.02%</small></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultEUR" name="defaultCurrency">
                                    <label class="custom-control-label" for="defaultEUR"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="EUR">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/gb.svg') center/cover;"></div>
                                    <span class="font-weight-bold">British Pound</span>
                                </div>
                            </td>
                            <td><code>GBP</code></td>
                            <td class="font-weight-bold">£</td>
                            <td>0.7832 <small class="text-danger">-0.15%</small></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultGBP" name="defaultCurrency">
                                    <label class="custom-control-label" for="defaultGBP"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="GBP">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/ae.svg') center/cover;"></div>
                                    <span class="font-weight-bold">UAE Dirham</span>
                                </div>
                            </td>
                            <td><code>AED</code></td>
                            <td class="font-weight-bold">د.إ</td>
                            <td>3.6725 <small class="text-muted">0.00%</small></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultAED" name="defaultCurrency">
                                    <label class="custom-control-label" for="defaultAED"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="AED">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/ca.svg') center/cover;"></div>
                                    <span class="font-weight-bold">Canadian Dollar</span>
                                </div>
                            </td>
                            <td><code>CAD</code></td>
                            <td class="font-weight-bold">C$</td>
                            <td>1.3542 <small class="text-success">+0.08%</small></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultCAD" name="defaultCurrency">
                                    <label class="custom-control-label" for="defaultCAD"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="CAD">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flag-icon mr-2" style="width: 24px; height: 16px; background: url('https://flagcdn.com/au.svg') center/cover;"></div>
                                    <span class="font-weight-bold">Australian Dollar</span>
                                </div>
                            </td>
                            <td><code>AUD</code></td>
                            <td class="font-weight-bold">A$</td>
                            <td>1.5198 <small class="text-danger">-0.05%</small></td>
                            <td><span class="badge badge-warning">Inactive</span></td>
                            <td>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="defaultAUD" name="defaultCurrency" disabled>
                                    <label class="custom-control-label" for="defaultAUD"></label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#editCurrencyModal" data-currency="AUD">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger">
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

    <!-- Exchange Rate Settings -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exchange Rate Settings</h6>
        </div>
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exchange Rate Provider</label>
                            <select class="form-control">
                                <option>Fixer.io</option>
                                <option>CurrencyLayer</option>
                                <option>ExchangeRates-API</option>
                                <option>Manual Entry</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Update Frequency</label>
                            <select class="form-control">
                                <option>Every hour</option>
                                <option>Every 4 hours</option>
                                <option>Daily</option>
                                <option>Manual only</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>API Key</label>
                            <input type="password" class="form-control" placeholder="Enter API key for exchange rate provider">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Rate Margin (%)</label>
                            <input type="number" class="form-control" step="0.01" value="0.5" placeholder="Add margin to exchange rates">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="autoUpdate" checked>
                        <label class="custom-control-label" for="autoUpdate">Automatically update exchange rates</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="fallbackRates" checked>
                        <label class="custom-control-label" for="fallbackRates">Use fallback rates if API fails</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>

<!-- Add Currency Modal -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Currency</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Currency</label>
                        <select class="form-control">
                            <option>Select a currency...</option>
                            <option value="JPY">Japanese Yen (JPY)</option>
                            <option value="CHF">Swiss Franc (CHF)</option>
                            <option value="CNY">Chinese Yuan (CNY)</option>
                            <option value="INR">Indian Rupee (INR)</option>
                            <option value="SGD">Singapore Dollar (SGD)</option>
                            <option value="NZD">New Zealand Dollar (NZD)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Currency Code</label>
                                <input type="text" class="form-control" placeholder="e.g., JPY" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Symbol</label>
                                <input type="text" class="form-control" placeholder="e.g., ¥">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Exchange Rate (to USD)</label>
                        <input type="number" class="form-control" step="0.0001" placeholder="Enter current exchange rate">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control">
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Currency</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Currency Modal -->
<div class="modal fade" id="editCurrencyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Currency</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Currency Name</label>
                        <input type="text" class="form-control" value="Euro" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Currency Code</label>
                                <input type="text" class="form-control" value="EUR" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Symbol</label>
                                <input type="text" class="form-control" value="€">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Exchange Rate (to USD)</label>
                        <input type="number" class="form-control" step="0.0001" value="0.8521">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control">
                            <option selected>Active</option>
                            <option>Inactive</option>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update exchange rates
    document.getElementById('updateRatesBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        this.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Update Exchange Rates';
            this.disabled = false;
            
            // Show success message
            alert('Exchange rates updated successfully!');
        }, 2000);
    });
});
</script>
@endpush
