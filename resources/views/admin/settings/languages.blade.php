@extends('layouts.admin')

@section('title', 'Language Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Language Settings</h1>
            <p class="mb-0 text-muted">Manage website languages and translations</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="importTranslations()">
                <i class="fas fa-upload me-2"></i>Import Translations
            </button>
            <button type="button" class="btn btn-outline-info" onclick="exportTranslations()">
                <i class="fas fa-download me-2"></i>Export Translations
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
                <i class="fas fa-plus me-2"></i>Add Language
            </button>
        </div>
    </div>

    <!-- Language Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Languages</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeLanguagesCount">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Translation Progress</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">87%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 87%" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-language fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Translation Keys</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Missing Translations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Language Management -->
    <div class="row">
        <!-- Languages List -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Supported Languages</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="detectMissingKeys()">Detect Missing Keys</a></li>
                            <li><a class="dropdown-item" href="#" onclick="autoTranslate()">Auto Translate</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="validateTranslations()">Validate All</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="languagesList">
                        <!-- English (Default) -->
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3 bg-light">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="https://flagcdn.com/w40/us.png" alt="English" class="rounded" style="width: 32px; height: 24px;">
                                </div>
                                <div>
                                    <h6 class="mb-0">English</h6>
                                    <small class="text-muted">en • Default Language</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success">100%</span>
                                <span class="badge bg-primary">Default</span>
                                <button class="btn btn-sm btn-outline-primary" onclick="editLanguage('en')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Arabic -->
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="https://flagcdn.com/w40/ae.png" alt="Arabic" class="rounded" style="width: 32px; height: 24px;">
                                </div>
                                <div>
                                    <h6 class="mb-0">العربية</h6>
                                    <small class="text-muted">ar • Right-to-Left</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning">89%</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked onchange="toggleLanguage('ar')">
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="editLanguage('ar')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteLanguage('ar')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- French -->
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="https://flagcdn.com/w40/fr.png" alt="French" class="rounded" style="width: 32px; height: 24px;">
                                </div>
                                <div>
                                    <h6 class="mb-0">Français</h6>
                                    <small class="text-muted">fr • Left-to-Right</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info">85%</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked onchange="toggleLanguage('fr')">
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="editLanguage('fr')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteLanguage('fr')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Translation Editor -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Translation Editor</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="translationFile" onchange="loadTranslationFile()">
                            <option value="general">General</option>
                            <option value="auth">Authentication</option>
                            <option value="bookings">Bookings</option>
                            <option value="properties">Properties</option>
                            <option value="emails">Email Templates</option>
                        </select>
                        <select class="form-select form-select-sm" id="editLanguage" onchange="loadTranslations()">
                            <option value="ar">العربية</option>
                            <option value="fr">Français</option>
                        </select>
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div id="translationEditor">
                        <!-- Translation pairs will be loaded here -->
                        <div class="mb-3">
                            <label class="form-label small text-muted">welcome_message</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="Welcome to HabibiStay" readonly>
                                    <small class="text-muted">English</small>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="أهلاً بكم في حبيبي ستاي" onchange="updateTranslation('welcome_message', this.value)">
                                    <small class="text-muted">Arabic</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">search_properties</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="Search Properties" readonly>
                                    <small class="text-muted">English</small>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="البحث عن العقارات" onchange="updateTranslation('search_properties', this.value)">
                                    <small class="text-muted">Arabic</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">book_now</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="Book Now" readonly>
                                    <small class="text-muted">English</small>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" value="احجز الآن" onchange="updateTranslation('book_now', this.value)">
                                    <small class="text-muted">Arabic</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="saveTranslations()">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Language Settings -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Language Configuration</h6>
                </div>
                <div class="card-body">
                    <form id="languageSettingsForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Default Language</label>
                                    <select class="form-select" name="default_language">
                                        <option value="en" selected>English</option>
                                        <option value="ar">Arabic</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fallback Language</label>
                                    <select class="form-select" name="fallback_language">
                                        <option value="en" selected>English</option>
                                        <option value="ar">Arabic</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="auto_detect_language" checked>
                                    <label class="form-check-label">Auto-detect user language</label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_language_switcher" checked>
                                    <label class="form-check-label">Show language switcher</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Translation Service</label>
                                    <select class="form-select" name="translation_service">
                                        <option value="none" selected>None</option>
                                        <option value="google">Google Translate</option>
                                        <option value="aws">AWS Translate</option>
                                        <option value="azure">Azure Translator</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Translation API Key</label>
                                    <input type="password" class="form-control" name="translation_api_key" placeholder="Enter API key">
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="cache_translations" checked>
                                    <label class="form-check-label">Cache translations</label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="rtl_support" checked>
                                    <label class="form-check-label">Enable RTL support</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Language Modal -->
<div class="modal fade" id="addLanguageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addLanguageForm">
                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <select class="form-select" name="language_code" required>
                            <option value="">Select Language</option>
                            <option value="es">Spanish</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="pt">Portuguese</option>
                            <option value="ru">Russian</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                            <option value="ko">Korean</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Native Name</label>
                        <input type="text" class="form-control" name="native_name" placeholder="e.g., Español" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Direction</label>
                        <select class="form-select" name="direction" required>
                            <option value="ltr">Left-to-Right</option>
                            <option value="rtl">Right-to-Left</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_translate">
                        <label class="form-check-label">Auto-translate from English</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addLanguage()">Add Language</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Language management functions
function addLanguage() {
    const form = document.getElementById('addLanguageForm');
    const formData = new FormData(form);
    
    // Show loading state
    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Success notification
        showNotification('Language added successfully!', 'success');
        
        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('addLanguageModal'));
        modal.hide();
        form.reset();
        
        // Refresh languages list
        loadLanguagesList();
        
        // Reset button
        btn.innerHTML = 'Add Language';
        btn.disabled = false;
    }, 1500);
}

function editLanguage(code) {
    // Implementation for editing language settings
    console.log('Editing language:', code);
}

function deleteLanguage(code) {
    if (confirm('Are you sure you want to delete this language? This action cannot be undone.')) {
        // Implementation for deleting language
        console.log('Deleting language:', code);
        showNotification('Language deleted successfully!', 'success');
        loadLanguagesList();
    }
}

function toggleLanguage(code) {
    // Implementation for enabling/disabling language
    console.log('Toggling language:', code);
    showNotification('Language status updated!', 'info');
}

function loadTranslationFile() {
    const file = document.getElementById('translationFile').value;
    const language = document.getElementById('editLanguage').value;
    
    // Implementation for loading translation file
    console.log('Loading translations for:', file, language);
}

function loadTranslations() {
    const language = document.getElementById('editLanguage').value;
    
    // Implementation for loading translations
    console.log('Loading translations for language:', language);
}

function updateTranslation(key, value) {
    // Implementation for updating translation
    console.log('Updating translation:', key, value);
}

function saveTranslations() {
    // Show loading state
    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Translations saved successfully!', 'success');
        
        // Reset button
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
        btn.disabled = false;
    }, 1000);
}

function importTranslations() {
    // Implementation for importing translations
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json,.csv,.xlsx';
    input.onchange = function(event) {
        const file = event.target.files[0];
        if (file) {
            console.log('Importing translations from:', file.name);
            showNotification('Translations imported successfully!', 'success');
        }
    };
    input.click();
}

function exportTranslations() {
    // Implementation for exporting translations
    console.log('Exporting translations...');
    showNotification('Translations exported successfully!', 'success');
}

function detectMissingKeys() {
    // Implementation for detecting missing translation keys
    console.log('Detecting missing keys...');
    showNotification('Missing keys detected and highlighted!', 'warning');
}

function autoTranslate() {
    if (confirm('This will automatically translate missing keys using the configured translation service. Continue?')) {
        console.log('Auto-translating missing keys...');
        showNotification('Auto-translation completed!', 'success');
    }
}

function validateTranslations() {
    console.log('Validating all translations...');
    showNotification('Translation validation completed!', 'info');
}

function loadLanguagesList() {
    // Implementation for refreshing languages list
    console.log('Refreshing languages list...');
}

// Language settings form handler
document.getElementById('languageSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const btn = e.target.querySelector('button[type="submit"]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Language settings saved successfully!', 'success');
        
        // Reset button
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Settings';
        btn.disabled = false;
    }, 1500);
});

// Utility function for notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadTranslationFile();
});
</script>
@endsection
