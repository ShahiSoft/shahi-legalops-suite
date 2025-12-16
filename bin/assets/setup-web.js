/**
 * ShahiTemplate Setup Web Interface
 * JavaScript for form handling, validation, and JSON generation
 */

(function() {
    'use strict';

    // State
    let currentStep = 1;
    const totalSteps = 6;
    let config = {};

    // DOM Elements
    const form = document.getElementById('setup-form');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const exportBtn = document.getElementById('export-btn');
    const importBtn = document.getElementById('import-btn');
    const copyBtn = document.getElementById('copy-btn');
    const importFile = document.getElementById('import-file');

    // Initialize
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        setupEventListeners();
        setupAutoGeneration();
        updateStep();
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        prevBtn.addEventListener('click', () => navigateStep(-1));
        nextBtn.addEventListener('click', () => navigateStep(1));
        exportBtn.addEventListener('click', exportConfig);
        importBtn.addEventListener('click', () => importFile.click());
        copyBtn.addEventListener('click', copyToClipboard);
        importFile.addEventListener('change', importConfig);

        // Color pickers
        const colorInputs = document.querySelectorAll('input[type="color"]');
        colorInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const textInput = e.target.nextElementSibling;
                if (textInput && textInput.classList.contains('color-text')) {
                    textInput.value = e.target.value;
                }
            });
        });
    }

    /**
     * Setup auto-generation of technical fields from plugin name
     */
    function setupAutoGeneration() {
        const pluginNameInput = document.getElementById('plugin_name');
        pluginNameInput.addEventListener('input', debounce(generateTechnicalFields, 300));
    }

    /**
     * Generate slug, namespace, and prefixes from plugin name
     */
    function generateTechnicalFields() {
        const pluginName = document.getElementById('plugin_name').value.trim();
        if (!pluginName) return;

        // Generate slug (lowercase, hyphens)
        const slug = pluginName
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        
        // Generate namespace (PascalCase)
        const namespace = pluginName
            .replace(/[^a-zA-Z0-9\s]/g, '')
            .split(/\s+/)
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join('');

        // Generate function prefix (lowercase + underscore)
        const functionPrefix = slug.replace(/-/g, '_') + '_';
        
        // Generate constant prefix (UPPERCASE + underscore)
        const constantPrefix = slug.toUpperCase().replace(/-/g, '_') + '_';
        
        // Generate CSS prefix (lowercase + hyphen)
        const cssPrefix = slug + '-';
        
        // Generate API namespace
        const apiNamespace = slug + '/v1';

        // Update fields
        document.getElementById('plugin_slug').value = slug;
        document.getElementById('namespace').value = namespace;
        document.getElementById('text_domain').value = slug;
        document.getElementById('function_prefix').value = functionPrefix;
        document.getElementById('constant_prefix').value = constantPrefix;
        document.getElementById('css_prefix').value = cssPrefix;
        document.getElementById('api_namespace').value = apiNamespace;
    }

    /**
     * Navigate between steps
     */
    function navigateStep(direction) {
        // Validate current step before moving forward
        if (direction > 0 && !validateCurrentStep()) {
            return;
        }

        currentStep += direction;
        if (currentStep < 1) currentStep = 1;
        if (currentStep > totalSteps) currentStep = totalSteps;

        updateStep();
    }

    /**
     * Update UI for current step
     */
    function updateStep() {
        // Update form steps
        const steps = document.querySelectorAll('.form-step');
        steps.forEach((step, index) => {
            step.classList.toggle('active', index + 1 === currentStep);
        });

        // Update progress bar
        const progressSteps = document.querySelectorAll('.progress-step');
        progressSteps.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.toggle('active', stepNum === currentStep);
            step.classList.toggle('completed', stepNum < currentStep);
        });

        // Update navigation buttons
        prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
        nextBtn.textContent = currentStep === totalSteps ? 'Finish' : 'Next →';

        // Generate preview on last step
        if (currentStep === totalSteps) {
            generatePreview();
        }
    }

    /**
     * Validate current step
     */
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const requiredInputs = currentStepElement.querySelectorAll('[required]');
        
        let isValid = true;
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                input.focus();
                input.style.borderColor = 'var(--error)';
                setTimeout(() => {
                    input.style.borderColor = '';
                }, 2000);
                isValid = false;
                return false;
            }
        });

        return isValid;
    }

    /**
     * Collect form data into config object
     */
    function collectFormData() {
        const formData = new FormData(form);
        config = {
            plugin_name: formData.get('plugin_name') || '',
            plugin_slug: formData.get('plugin_slug') || '',
            description: formData.get('description') || '',
            namespace: formData.get('namespace') || '',
            text_domain: formData.get('text_domain') || '',
            function_prefix: formData.get('function_prefix') || '',
            constant_prefix: formData.get('constant_prefix') || '',
            css_prefix: formData.get('css_prefix') || '',
            version: formData.get('version') || '1.0.0',
            author_name: formData.get('author_name') || '',
            author_email: formData.get('author_email') || '',
            author_url: formData.get('author_url') || '',
            license: formData.get('license') || 'GPL-3.0-or-later',
            min_wp_version: formData.get('min_wp_version') || '5.8',
            min_php_version: formData.get('min_php_version') || '7.4',
            repository_url: formData.get('repository_url') || '',
            api_namespace: formData.get('api_namespace') || '',
            menu_position: parseInt(formData.get('menu_position')) || 6,
            menu_icon: formData.get('menu_icon') || 'dashicons-admin-generic',
            colors: {
                primary: formData.get('colors[primary]') || '#00d4ff',
                secondary: formData.get('colors[secondary]') || '#7000ff',
                accent: formData.get('colors[accent]') || '#00ff88',
                background_dark: formData.get('colors[background_dark]') || '#0a0a12',
                background_light: formData.get('colors[background_light]') || '#1a1a2e'
            },
            theme: formData.get('theme') || 'neon-aether',
            features: {
                analytics: formData.get('features[analytics]') === 'on',
                cache: formData.get('features[cache]') === 'on',
                seo: formData.get('features[seo]') === 'on',
                security: formData.get('features[security]') === 'on',
                custom_post_types: formData.get('features[custom_post_types]') === 'on'
            },
            dev_options: {
                remove_examples: formData.get('dev_options[remove_examples]') === 'on',
                remove_docs: formData.get('dev_options[remove_docs]') === 'on',
                create_git: formData.get('dev_options[create_git]') === 'on',
                composer_install: formData.get('dev_options[composer_install]') === 'on'
            }
        };

        return config;
    }

    /**
     * Generate configuration preview
     */
    function generatePreview() {
        collectFormData();
        const preview = document.getElementById('config-preview');
        preview.textContent = JSON.stringify(config, null, 2);
    }

    /**
     * Export configuration as JSON file
     */
    function exportConfig() {
        collectFormData();
        
        const dataStr = JSON.stringify(config, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'setup-config.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showNotification('Configuration downloaded successfully!', 'success');
    }

    /**
     * Import configuration from JSON file
     */
    function importConfig(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const importedConfig = JSON.parse(event.target.result);
                populateForm(importedConfig);
                showNotification('Configuration imported successfully!', 'success');
            } catch (error) {
                showNotification('Invalid JSON file!', 'error');
            }
        };
        reader.readAsText(file);
    }

    /**
     * Populate form with imported data
     */
    function populateForm(data) {
        // Core info
        if (data.plugin_name) document.getElementById('plugin_name').value = data.plugin_name;
        if (data.plugin_slug) document.getElementById('plugin_slug').value = data.plugin_slug;
        if (data.description) document.getElementById('description').value = data.description;
        if (data.version) document.getElementById('version').value = data.version;
        if (data.min_wp_version) document.getElementById('min_wp_version').value = data.min_wp_version;
        if (data.min_php_version) document.getElementById('min_php_version').value = data.min_php_version;

        // Author
        if (data.author_name) document.getElementById('author_name').value = data.author_name;
        if (data.author_email) document.getElementById('author_email').value = data.author_email;
        if (data.author_url) document.getElementById('author_url').value = data.author_url;
        if (data.repository_url) document.getElementById('repository_url').value = data.repository_url;
        if (data.license) document.getElementById('license').value = data.license;

        // Technical
        if (data.namespace) document.getElementById('namespace').value = data.namespace;
        if (data.text_domain) document.getElementById('text_domain').value = data.text_domain;
        if (data.function_prefix) document.getElementById('function_prefix').value = data.function_prefix;
        if (data.constant_prefix) document.getElementById('constant_prefix').value = data.constant_prefix;
        if (data.css_prefix) document.getElementById('css_prefix').value = data.css_prefix;
        if (data.api_namespace) document.getElementById('api_namespace').value = data.api_namespace;
        if (data.menu_position) document.getElementById('menu_position').value = data.menu_position;
        if (data.menu_icon) document.getElementById('menu_icon').value = data.menu_icon;

        // Colors
        if (data.colors) {
            if (data.colors.primary) {
                document.getElementById('color_primary').value = data.colors.primary;
                document.getElementById('color_primary').nextElementSibling.value = data.colors.primary;
            }
            if (data.colors.secondary) {
                document.getElementById('color_secondary').value = data.colors.secondary;
                document.getElementById('color_secondary').nextElementSibling.value = data.colors.secondary;
            }
            if (data.colors.accent) {
                document.getElementById('color_accent').value = data.colors.accent;
                document.getElementById('color_accent').nextElementSibling.value = data.colors.accent;
            }
            if (data.colors.background_dark) {
                document.getElementById('color_bg_dark').value = data.colors.background_dark;
                document.getElementById('color_bg_dark').nextElementSibling.value = data.colors.background_dark;
            }
            if (data.colors.background_light) {
                document.getElementById('color_bg_light').value = data.colors.background_light;
                document.getElementById('color_bg_light').nextElementSibling.value = data.colors.background_light;
            }
        }

        // Theme
        if (data.theme) {
            const themeRadio = document.getElementById('theme-' + data.theme);
            if (themeRadio) themeRadio.checked = true;
        }

        // Features
        if (data.features) {
            document.querySelector('input[name="features[analytics]"]').checked = data.features.analytics || false;
            document.querySelector('input[name="features[cache]"]').checked = data.features.cache || false;
            document.querySelector('input[name="features[seo]"]').checked = data.features.seo || false;
            document.querySelector('input[name="features[security]"]').checked = data.features.security || false;
            if (data.features.custom_post_types !== undefined) {
                document.querySelector('input[name="features[custom_post_types]"]').checked = data.features.custom_post_types;
            }
        }

        // Dev Options
        if (data.dev_options) {
            if (data.dev_options.remove_examples !== undefined) {
                document.querySelector('input[name="dev_options[remove_examples]"]').checked = data.dev_options.remove_examples;
            }
            if (data.dev_options.remove_docs !== undefined) {
                document.querySelector('input[name="dev_options[remove_docs]"]').checked = data.dev_options.remove_docs;
            }
            if (data.dev_options.create_git !== undefined) {
                document.querySelector('input[name="dev_options[create_git]"]').checked = data.dev_options.create_git;
            }
            if (data.dev_options.composer_install !== undefined) {
                document.querySelector('input[name="dev_options[composer_install]"]').checked = data.dev_options.composer_install;
            }
        }
    }

    /**
     * Copy configuration to clipboard
     */
    function copyToClipboard() {
        collectFormData();
        const dataStr = JSON.stringify(config, null, 2);
        
        navigator.clipboard.writeText(dataStr).then(() => {
            showNotification('Configuration copied to clipboard!', 'success');
        }).catch(() => {
            showNotification('Failed to copy to clipboard!', 'error');
        });
    }

    /**
     * Show notification toast
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: ${type === 'success' ? 'var(--success)' : 'var(--error)'};
            color: var(--bg-dark);
            border-radius: var(--radius);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            z-index: 9999;
            font-weight: 600;
            animation: slideIn 0.3s;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    /**
     * Debounce utility function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add animations to CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

})();
