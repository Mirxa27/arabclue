@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark font-weight-bold mb-1">Settings</h2>
                    <p class="text-muted mb-0">Configure application settings and preferences</p>
                </div>
                <div>
                    <button class="btn btn-success" onclick="saveAllSettings()">
                        <i class="fas fa-save me-2"></i>Save All Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Settings Navigation -->
        <div class="col-md-3 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Settings Categories</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#general" class="list-group-item list-group-item-action active" onclick="showSettingsTab('general', this)">
                        <i class="fas fa-cog me-2"></i>General Settings
                    </a>
                    <a href="#payment" class="list-group-item list-group-item-action" onclick="showSettingsTab('payment', this)">
                        <i class="fas fa-credit-card me-2"></i>Payment Settings
                    </a>
                    <a href="#email" class="list-group-item list-group-item-action" onclick="showSettingsTab('email', this)">
                        <i class="fas fa-envelope me-2"></i>Email Settings
                    </a>
                    <a href="#booking" class="list-group-item list-group-item-action" onclick="showSettingsTab('booking', this)">
                        <i class="fas fa-calendar me-2"></i>Booking Settings
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" onclick="showSettingsTab('security', this)">
                        <i class="fas fa-shield-alt me-2"></i>Security Settings
                    </a>
                    <a href="#maintenance" class="list-group-item list-group-item-action" onclick="showSettingsTab('maintenance', this)">
                        <i class="fas fa-tools me-2"></i>Maintenance
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <!-- General Settings -->
            <div id="general-settings" class="settings-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="site-name">Site Name</label>
                                        <input type="text" class="form-control" id="site-name" value="HabibiStay">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="site-tagline">Site Tagline</label>
                                        <input type="text" class="form-control" id="site-tagline" value="Your Home Away From Home">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="site-description">Site Description</label>
                                <textarea class="form-control" id="site-description" rows="3">Discover unique accommodations and unforgettable stays with HabibiStay.</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact-email">Contact Email</label>
                                        <input type="email" class="form-control" id="contact-email" value="info@habibistay.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact-phone">Contact Phone</label>
                                        <input type="tel" class="form-control" id="contact-phone" value="+1234567890">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="timezone">Default Timezone</label>
                                        <select class="form-control" id="timezone">
                                            <option value="UTC">UTC</option>
                                            <option value="America/New_York" selected>Eastern Time</option>
                                            <option value="America/Chicago">Central Time</option>
                                            <option value="America/Denver">Mountain Time</option>
                                            <option value="America/Los_Angeles">Pacific Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency">Default Currency</label>
                                        <select class="form-control" id="currency">
                                            <option value="USD" selected>USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="CAD">CAD - Canadian Dollar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div id="payment-settings" class="settings-tab" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Payment Gateway Settings</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="stripe-enabled" checked>
                                    <label class="custom-control-label" for="stripe-enabled">Enable Stripe Payments</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stripe-public-key">Stripe Publishable Key</label>
                                        <input type="text" class="form-control" id="stripe-public-key" placeholder="pk_test_...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stripe-secret-key">Stripe Secret Key</label>
                                        <input type="password" class="form-control" id="stripe-secret-key" placeholder="sk_test_...">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="paypal-enabled">
                                    <label class="custom-control-label" for="paypal-enabled">Enable PayPal Payments</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paypal-client-id">PayPal Client ID</label>
                                        <input type="text" class="form-control" id="paypal-client-id">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paypal-secret">PayPal Client Secret</label>
                                        <input type="password" class="form-control" id="paypal-secret">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="payment-fee">Platform Fee (%)</label>
                                <input type="number" class="form-control" id="payment-fee" value="3.5" step="0.1" min="0" max="100">
                                <small class="form-text text-muted">Percentage fee charged on each booking</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div id="email-settings" class="settings-tab" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Email Configuration</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail-driver">Mail Driver</label>
                                        <select class="form-control" id="mail-driver">
                                            <option value="smtp" selected>SMTP</option>
                                            <option value="mailgun">Mailgun</option>
                                            <option value="ses">Amazon SES</option>
                                            <option value="sendmail">Sendmail</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail-from-address">From Email Address</label>
                                        <input type="email" class="form-control" id="mail-from-address" value="noreply@habibistay.com">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp-host">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp-host" value="smtp.gmail.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp-port">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp-port" value="587">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp-username">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp-username">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp-password">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp-password">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="smtp-encryption" checked>
                                    <label class="custom-control-label" for="smtp-encryption">Use TLS Encryption</label>
                                </div>
                            </div>
                            <button type="button" class="btn btn-info" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane me-2"></i>Send Test Email
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Booking Settings -->
            <div id="booking-settings" class="settings-tab" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Booking Configuration</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="booking-advance-days">Maximum Advance Booking (Days)</label>
                                        <input type="number" class="form-control" id="booking-advance-days" value="365" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="minimum-stay">Minimum Stay (Nights)</label>
                                        <input type="number" class="form-control" id="minimum-stay" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cancellation-policy">Default Cancellation Policy</label>
                                        <select class="form-control" id="cancellation-policy">
                                            <option value="flexible">Flexible</option>
                                            <option value="moderate" selected>Moderate</option>
                                            <option value="strict">Strict</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check-in-time">Default Check-in Time</label>
                                        <input type="time" class="form-control" id="check-in-time" value="15:00">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check-out-time">Default Check-out Time</label>
                                        <input type="time" class="form-control" id="check-out-time" value="11:00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cleaning-fee">Default Cleaning Fee</label>
                                        <input type="number" class="form-control" id="cleaning-fee" value="25" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="instant-booking" checked>
                                    <label class="custom-control-label" for="instant-booking">Enable Instant Booking by Default</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="auto-approve">
                                    <label class="custom-control-label" for="auto-approve">Auto-approve Booking Requests</label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div id="security-settings" class="settings-tab" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Security Configuration</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="two-factor-auth" checked>
                                    <label class="custom-control-label" for="two-factor-auth">Require Two-Factor Authentication for Admins</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="force-https" checked>
                                    <label class="custom-control-label" for="force-https">Force HTTPS Connections</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="session-timeout">Session Timeout (Minutes)</label>
                                        <input type="number" class="form-control" id="session-timeout" value="120" min="5">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max-login-attempts">Max Login Attempts</label>
                                        <input type="number" class="form-control" id="max-login-attempts" value="5" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="allowed-file-types">Allowed Upload File Types</label>
                                <input type="text" class="form-control" id="allowed-file-types" value="jpg,jpeg,png,gif,pdf,doc,docx">
                                <small class="form-text text-muted">Comma-separated list of allowed file extensions</small>
                            </div>
                            <div class="form-group">
                                <label for="max-file-size">Maximum File Upload Size (MB)</label>
                                <input type="number" class="form-control" id="max-file-size" value="10" min="1">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Maintenance Settings -->
            <div id="maintenance-settings" class="settings-tab" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Maintenance & System</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="maintenance-mode">
                                        <label class="custom-control-label" for="maintenance-mode">Enable Maintenance Mode</label>
                                    </div>
                                    <small class="form-text text-muted">Temporarily disable site access for maintenance</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="debug-mode">
                                        <label class="custom-control-label" for="debug-mode">Enable Debug Mode</label>
                                    </div>
                                    <small class="form-text text-muted">Show detailed error messages (disable in production)</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="maintenance-message">Maintenance Message</label>
                            <textarea class="form-control" id="maintenance-message" rows="3">We're currently performing scheduled maintenance. Please check back soon!</textarea>
                        </div>
                        <hr>
                        <h6 class="font-weight-bold mb-3">Cache Management</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-warning btn-block" onclick="clearCache('config')">
                                    <i class="fas fa-trash me-2"></i>Clear Config Cache
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-warning btn-block" onclick="clearCache('route')">
                                    <i class="fas fa-trash me-2"></i>Clear Route Cache
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-warning btn-block" onclick="clearCache('view')">
                                    <i class="fas fa-trash me-2"></i>Clear View Cache
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-danger btn-block" onclick="clearCache('all')">
                                    <i class="fas fa-trash me-2"></i>Clear All Cache
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSettingsTab(tabName, element) {
    // Hide all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-settings').style.display = 'block';
    
    // Add active class to clicked nav item
    element.classList.add('active');
}

function saveAllSettings() {
    // Simulate saving all settings
    const loadingBtn = event.target;
    const originalText = loadingBtn.innerHTML;
    loadingBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    loadingBtn.disabled = true;
    
    setTimeout(() => {
        loadingBtn.innerHTML = originalText;
        loadingBtn.disabled = false;
        alert('All settings saved successfully!');
    }, 2000);
}

function testEmailSettings() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Test email sent successfully!');
    }, 3000);
}

function clearCache(type) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Clearing...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert(`${type.charAt(0).toUpperCase() + type.slice(1)} cache cleared successfully!`);
    }, 1500);
}
</script>
@endsection
