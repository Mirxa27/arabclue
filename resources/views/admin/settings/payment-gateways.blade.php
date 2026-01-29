@extends('layouts.admin')

@section('title', 'Payment Gateways')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Payment Gateways</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
                    <li class="breadcrumb-item active">Payment Gateways</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success" data-toggle="modal" data-target="#addGatewayModal">
                <i class="fas fa-plus"></i> Add Gateway
            </button>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Gateways</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Success Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">98.5%</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monthly Volume</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$245K</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg. Fee</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2.9%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Gateways -->
    <div class="row">
        <!-- Stripe -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="gateway-logo mr-3" style="width: 40px; height: 40px; background: #635bff; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-stripe text-white"></i>
                        </div>
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary">Stripe</h6>
                            <small class="text-muted">Credit Cards, Wallets</small>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="stripeEnabled" checked>
                        <label class="custom-control-label" for="stripeEnabled"></label>
                    </div>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label>Publishable Key</label>
                            <input type="text" class="form-control" placeholder="pk_live_..." value="pk_live_51H...">
                        </div>
                        <div class="form-group">
                            <label>Secret Key</label>
                            <input type="password" class="form-control" placeholder="sk_live_..." value="sk_live_51H...">
                        </div>
                        <div class="form-group">
                            <label>Webhook Secret</label>
                            <input type="password" class="form-control" placeholder="whsec_..." value="whsec_1H...">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Environment</label>
                                    <select class="form-control">
                                        <option>Production</option>
                                        <option>Test</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Percentage</label>
                                    <input type="number" class="form-control" step="0.01" value="2.9">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge badge-success">Connected</span>
                                <span class="badge badge-info ml-1">Primary</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary">Test Connection</button>
                                <button type="button" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PayPal -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="gateway-logo mr-3" style="width: 40px; height: 40px; background: #0070ba; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-paypal text-white"></i>
                        </div>
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary">PayPal</h6>
                            <small class="text-muted">PayPal, Credit Cards</small>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="paypalEnabled" checked>
                        <label class="custom-control-label" for="paypalEnabled"></label>
                    </div>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label>Client ID</label>
                            <input type="text" class="form-control" placeholder="AQkquBDf1zctJO..." value="AQkquBDf1z...">
                        </div>
                        <div class="form-group">
                            <label>Client Secret</label>
                            <input type="password" class="form-control" placeholder="ELtVh_..." value="ELtVh_...">
                        </div>
                        <div class="form-group">
                            <label>Webhook ID</label>
                            <input type="text" class="form-control" placeholder="8JR59049..." value="8JR59049...">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Environment</label>
                                    <select class="form-control">
                                        <option>Production</option>
                                        <option>Sandbox</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Percentage</label>
                                    <input type="number" class="form-control" step="0.01" value="3.4">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge badge-success">Connected</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary">Test Connection</button>
                                <button type="button" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Square -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="gateway-logo mr-3" style="width: 40px; height: 40px; background: #000; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-square text-white"></i>
                        </div>
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary">Square</h6>
                            <small class="text-muted">Credit Cards, Digital Wallets</small>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="squareEnabled">
                        <label class="custom-control-label" for="squareEnabled"></label>
                    </div>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label>Application ID</label>
                            <input type="text" class="form-control" placeholder="sq0idp-...">
                        </div>
                        <div class="form-group">
                            <label>Access Token</label>
                            <input type="password" class="form-control" placeholder="EAAAEOuLj...">
                        </div>
                        <div class="form-group">
                            <label>Location ID</label>
                            <input type="text" class="form-control" placeholder="LH2G2WXW...">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Environment</label>
                                    <select class="form-control">
                                        <option>Production</option>
                                        <option>Sandbox</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Percentage</label>
                                    <input type="number" class="form-control" step="0.01" value="2.6">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge badge-secondary">Not Connected</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary">Test Connection</button>
                                <button type="button" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Razorpay -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="gateway-logo mr-3" style="width: 40px; height: 40px; background: #528ff0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <span class="text-white font-weight-bold" style="font-size: 12px;">RZ</span>
                        </div>
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary">Razorpay</h6>
                            <small class="text-muted">Cards, UPI, Wallets, Banking</small>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="razorpayEnabled">
                        <label class="custom-control-label" for="razorpayEnabled"></label>
                    </div>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label>Key ID</label>
                            <input type="text" class="form-control" placeholder="rzp_live_...">
                        </div>
                        <div class="form-group">
                            <label>Key Secret</label>
                            <input type="password" class="form-control" placeholder="...">
                        </div>
                        <div class="form-group">
                            <label>Webhook Secret</label>
                            <input type="password" class="form-control" placeholder="...">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Environment</label>
                                    <select class="form-control">
                                        <option>Production</option>
                                        <option>Test</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fee Percentage</label>
                                    <input type="number" class="form-control" step="0.01" value="2.0">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge badge-secondary">Not Connected</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary">Test Connection</button>
                                <button type="button" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gateway Settings -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">General Payment Settings</h6>
        </div>
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Default Currency</label>
                            <select class="form-control">
                                <option>USD - US Dollar</option>
                                <option>EUR - Euro</option>
                                <option>GBP - British Pound</option>
                                <option>AED - UAE Dirham</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Payment Timeout (minutes)</label>
                            <input type="number" class="form-control" value="15">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Platform Fee (%)</label>
                            <input type="number" class="form-control" step="0.01" value="5.0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Host Payout Schedule</label>
                            <select class="form-control">
                                <option>After guest checkout</option>
                                <option>24 hours after checkout</option>
                                <option>Weekly</option>
                                <option>Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="saveCards" checked>
                        <label class="custom-control-label" for="saveCards">Allow guests to save payment methods</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="autoCapture" checked>
                        <label class="custom-control-label" for="autoCapture">Auto-capture payments on booking confirmation</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="refundPolicy">
                        <label class="custom-control-label" for="refundPolicy">Enable automated refunds based on cancellation policy</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save General Settings</button>
            </form>
        </div>
    </div>
</div>

<!-- Add Gateway Modal -->
<div class="modal fade" id="addGatewayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment Gateway</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Gateway Provider</label>
                        <select class="form-control">
                            <option>Select a provider...</option>
                            <option>Stripe</option>
                            <option>PayPal</option>
                            <option>Square</option>
                            <option>Razorpay</option>
                            <option>Mollie</option>
                            <option>Adyen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" class="form-control" placeholder="e.g., Credit Card">
                    </div>
                    <div class="form-group">
                        <label>Environment</label>
                        <select class="form-control">
                            <option>Test/Sandbox</option>
                            <option>Production</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Gateway</button>
            </div>
        </div>
    </div>
</div>

@endsection
