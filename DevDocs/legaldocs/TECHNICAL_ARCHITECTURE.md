# Legal Documents Generator - Technical Architecture & Development Guide

**Document Version:** 1.0  
**Last Updated:** December 17, 2025  
**Target Audience:** Development Team, Architects  
**PHP Version:** 8.0+  
**Framework:** ShahiComplyFlow Core Architecture  

---

## 📁 MODULE STRUCTURE

```
includes/Modules/LegalDocumentsGenerator/
├── LegalDocumentsModule.php           # Main module class
├── Config/
│   ├── DocumentTypes.php              # Document type registry
│   ├── Regulations.php                # Regulatory requirements
│   ├── QuestionnaireDefinitions.php   # Smart questions structure
│   └── ContentTemplates.php           # Template library
├── Services/
│   ├── DocumentGenerationService.php  # Core generation engine
│   ├── AIContentService.php           # AI-powered content
│   ├── RegulatoryService.php          # Real-time compliance tracking
│   ├── QuestionnaireService.php       # Smart Q&A engine
│   ├── CustomizationService.php       # Visual/code editor
│   ├── PublishingService.php          # Deploy to website
│   ├── ComplianceValidationService.php # Audit & validation
│   └── IntegrationService.php         # WooCommerce, etc.
├── Controllers/
│   ├── DocumentController.php         # CRUD operations
│   ├── GenerationController.php       # Generation API
│   ├── CustomizationController.php    # Edit operations
│   ├── PublishingController.php       # Publish operations
│   └── ValidationController.php       # Compliance checks
├── Models/
│   ├── Document.php                   # Document entity
│   ├── DocumentVersion.php            # Version history
│   ├── DocumentSection.php            # Section data
│   ├── QuestionResponse.php           # Question answers
│   └── ComplianceIssue.php            # Validation results
├── Repositories/
│   ├── DocumentRepository.php         # Database access
│   ├── VersionRepository.php          # Version management
│   └── ComplianceRepository.php       # Compliance data
├── Admin/
│   ├── DocumentListTable.php          # Documents admin table
│   ├── SettingsPage.php               # Module settings
│   ├── DocumentEditor.php             # Editor main page
│   └── Menu.php                       # Admin menu setup
├── Templates/
│   ├── admin/
│   │   ├── document-editor.php
│   │   ├── questionnaire.php
│   │   ├── customization-editor.php
│   │   ├── code-editor.php
│   │   ├── preview.php
│   │   ├── publish.php
│   │   ├── versions.php
│   │   └── compliance-report.php
│   └── documents/
│       ├── privacy-policy.php
│       ├── terms-of-service.php
│       ├── cookie-policy.php
│       └── [other document templates]
├── Assets/
│   ├── css/
│   │   └── legal-documents-editor.css
│   ├── js/
│   │   ├── questionnaire-engine.js
│   │   ├── visual-editor.js
│   │   ├── code-editor.js
│   │   └── preview-generator.js
│   └── images/
│       └── document-icons.svg
├── REST/
│   ├── DocumentController.php         # REST endpoints
│   ├── ValidationController.php
│   └── routes.php                     # Route definitions
├── Tests/
│   ├── Unit/
│   │   ├── DocumentGenerationServiceTest.php
│   │   ├── RegulatoryServiceTest.php
│   │   └── ValidationServiceTest.php
│   └── Integration/
│       ├── DocumentPublishingTest.php
│       └── WooCommerceIntegrationTest.php
├── Database/
│   ├── Migrations/
│   │   ├── create_documents_table.php
│   │   ├── create_document_versions_table.php
│   │   └── create_compliance_issues_table.php
│   └── Schema.php                     # Database schema definition
├── Hooks/
│   ├── Filters.php                    # Available filters
│   └── Actions.php                    # Available actions
└── Exceptions/
    ├── DocumentException.php
    ├── GenerationException.php
    └── ValidationException.php
```

---

## 🗄️ DATABASE SCHEMA

### Table: `complyflow_documents`
```sql
CREATE TABLE complyflow_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    document_type VARCHAR(50) NOT NULL,           -- 'privacy-policy', 'terms', etc.
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    status VARCHAR(20),                          -- 'draft', 'review', 'approved', 'published'
    content LONGTEXT,                            -- Serialized content array
    settings JSON,                               -- Document-specific settings
    jurisdiction VARCHAR(20),                    -- Country/state code
    language VARCHAR(10),                        -- Language code
    business_data JSON,                          -- Auto-detected business info
    questionnaire_responses JSON,                -- Saved Q&A responses
    created_by BIGINT UNSIGNED,                  -- User ID
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    published_at TIMESTAMP NULL,
    published_by BIGINT UNSIGNED NULL,
    archived_at TIMESTAMP NULL,
    page_id BIGINT UNSIGNED NULL,                -- Linked WordPress page
    compliance_score INT DEFAULT 0,              -- 0-100 score
    compliance_issues INT DEFAULT 0,             -- Count of issues
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    FOREIGN KEY (published_by) REFERENCES wp_users(ID),
    INDEX idx_document_type (document_type),
    INDEX idx_status (status),
    INDEX idx_jurisdiction (jurisdiction),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_title_description (title, description)
);
```

### Table: `complyflow_document_versions`
```sql
CREATE TABLE complyflow_document_versions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number INT NOT NULL,
    title VARCHAR(255),
    content LONGTEXT,
    change_reason VARCHAR(255),
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    archived BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (document_id) REFERENCES complyflow_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES wp_users(ID),
    UNIQUE KEY unique_version (document_id, version_number),
    INDEX idx_document_id (document_id),
    INDEX idx_created_at (created_at)
);
```

### Table: `complyflow_document_sections`
```sql
CREATE TABLE complyflow_document_sections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    document_id BIGINT UNSIGNED NOT NULL,
    section_key VARCHAR(100) NOT NULL,           -- 'intro', 'data-collection', etc.
    section_title VARCHAR(255),
    section_content LONGTEXT,
    is_custom BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    is_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES complyflow_documents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_section (document_id, section_key),
    INDEX idx_document_id (document_id),
    INDEX idx_sort_order (sort_order)
);
```

### Table: `complyflow_compliance_issues`
```sql
CREATE TABLE complyflow_compliance_issues (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    document_id BIGINT UNSIGNED NOT NULL,
    issue_type VARCHAR(50),                      -- 'missing-clause', 'enforceability', 'best-practice'
    severity VARCHAR(20),                        -- 'critical', 'warning', 'info'
    regulation VARCHAR(50),                      -- Applicable regulation
    message TEXT,
    suggested_fix TEXT,
    section_id BIGINT UNSIGNED,
    status VARCHAR(20) DEFAULT 'unresolved',     -- 'resolved', 'ignored', 'unresolved'
    created_at TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by BIGINT UNSIGNED NULL,
    FOREIGN KEY (document_id) REFERENCES complyflow_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES complyflow_document_sections(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES wp_users(ID),
    INDEX idx_document_id (document_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status)
);
```

### Table: `complyflow_regulatory_updates`
```sql
CREATE TABLE complyflow_regulatory_updates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    regulation_key VARCHAR(100) NOT NULL,        -- 'GDPR', 'CCPA', etc.
    jurisdiction VARCHAR(20),
    update_title VARCHAR(255),
    update_description LONGTEXT,
    effective_date DATE,
    status VARCHAR(20),                          -- 'upcoming', 'active', 'archived'
    affected_document_types TEXT,                -- JSON array of document types
    required_actions TEXT,                       -- Implementation guidance
    source_url VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_jurisdiction (jurisdiction),
    INDEX idx_effective_date (effective_date),
    INDEX idx_regulation_key (regulation_key)
);
```

---

## 🔌 API ENDPOINTS

### REST API (Namespace: `complyflow/v1`)

#### Document CRUD
```
GET    /documents                    # List all documents
POST   /documents                    # Create new document
GET    /documents/{id}               # Get document details
PUT    /documents/{id}               # Update document
DELETE /documents/{id}               # Delete document
GET    /documents/{id}/versions      # Get version history
POST   /documents/{id}/versions/{versionId}/restore  # Restore version
POST   /documents/{id}/compare       # Compare two versions
```

#### Generation & Customization
```
POST   /documents/generate           # AI-generate new document
POST   /documents/{id}/questionnaire # Update Q&A responses
PUT    /documents/{id}/content       # Update content
PUT    /documents/{id}/sections/{sectionKey}  # Update section
POST   /documents/{id}/sections      # Add custom section
```

#### Publishing
```
POST   /documents/{id}/publish       # Publish document
POST   /documents/{id}/unpublish     # Unpublish document
POST   /documents/{id}/preview       # Get HTML preview
GET    /documents/{id}/published-page # Get published page URL
```

#### Validation & Compliance
```
POST   /documents/{id}/validate      # Validate compliance
GET    /documents/{id}/compliance-report  # Get audit report
GET    /documents/{id}/compliance-score   # Get compliance score
```

#### Regulatory Tracking
```
GET    /regulatory-updates           # List all regulatory updates
GET    /regulatory-updates/{jurisdiction}  # Get updates for jurisdiction
POST   /regulatory-updates/check     # Check if document needs updating
```

---

## 🎯 CORE SERVICES

### DocumentGenerationService
```php
class DocumentGenerationService {
    /**
     * Generate document from questionnaire responses
     */
    public function generateDocument(
        string $documentType,
        array $questionnaireResponses,
        string $jurisdiction,
        string $language = 'en'
    ): Document;
    
    /**
     * Get available document types
     */
    public function getAvailableDocumentTypes(): array;
    
    /**
     * Get template for document type
     */
    public function getTemplate(string $documentType, string $jurisdiction): Template;
    
    /**
     * Assemble sections into complete document
     */
    public function assembleDocument(array $sections): string;
}
```

### AIContentService
```php
class AIContentService {
    /**
     * Improve content using AI
     */
    public function improveContent(string $content, string $context = ''): string;
    
    /**
     * Generate section suggestions
     */
    public function suggestMissingClauses(Document $document): array;
    
    /**
     * Simplify complex legal language
     */
    public function simplifyContent(string $content): string;
    
    /**
     * Extract key points for summary
     */
    public function generateSummary(Document $document): string;
}
```

### RegulatoryService
```php
class RegulatoryService {
    /**
     * Get all applicable regulations for jurisdiction
     */
    public function getApplicableRegulations(string $jurisdiction): array;
    
    /**
     * Get requirements for specific regulation
     */
    public function getRegulatoryRequirements(string $regulation): array;
    
    /**
     * Check for recent regulatory changes
     */
    public function checkForUpdates(string $jurisdiction): array;
    
    /**
     * Get mapping of regulation to required clauses
     */
    public function getMappingMatrix(string $documentType, string $jurisdiction): array;
}
```

### QuestionnaireService
```php
class QuestionnaireService {
    /**
     * Get questionnaire definition for document type
     */
    public function getQuestionnaire(string $documentType): Questionnaire;
    
    /**
     * Validate questionnaire responses
     */
    public function validateResponses(array $responses): ValidationResult;
    
    /**
     * Get conditional follow-up questions
     */
    public function getFollowUpQuestions(array $currentResponses): array;
    
    /**
     * Save responses
     */
    public function saveResponses(Document $document, array $responses): void;
}
```

### CustomizationService
```php
class CustomizationService {
    /**
     * Update section content
     */
    public function updateSection(Document $document, string $sectionKey, string $content): void;
    
    /**
     * Add custom section
     */
    public function addCustomSection(Document $document, Section $section): void;
    
    /**
     * Reorder sections
     */
    public function reorderSections(Document $document, array $order): void;
    
    /**
     * Replace template variables
     */
    public function replaceVariables(string $content, array $variables): string;
}
```

### PublishingService
```php
class PublishingService {
    /**
     * Publish document to WordPress page
     */
    public function publishToPage(Document $document, array $options = []): int;
    
    /**
     * Publish via shortcode
     */
    public function getShortcode(Document $document): string;
    
    /**
     * Export to PDF
     */
    public function exportToPDF(Document $document): string;
    
    /**
     * Get published URL
     */
    public function getPublishedURL(Document $document): string;
}
```

### ComplianceValidationService
```php
class ComplianceValidationService {
    /**
     * Validate document against regulations
     */
    public function validateCompliance(Document $document): ComplianceReport;
    
    /**
     * Calculate compliance score
     */
    public function calculateScore(Document $document): int;
    
    /**
     * Get missing clauses
     */
    public function getMissingClauses(Document $document): array;
    
    /**
     * Check enforceability
     */
    public function checkEnforceability(Document $document): array;
}
```

---

## 🔗 INTEGRATION POINTS

### WooCommerce Integration
```php
class WooCommerceIntegration implements IntegrationInterface {
    
    public function detectProductTypes(): array {
        // Return array of physical/digital/subscription
    }
    
    public function getShippingZones(): array {
        // Return shipping zone data
    }
    
    public function getPaymentGateways(): array {
        // Return enabled payment methods
    }
    
    public function autoFillRefundPolicy(): array {
        // Return pre-filled refund policy data
    }
    
    public function getReturnSettings(): array {
        // Return return policy from WooCommerce settings
    }
}
```

### Form Plugins Integration
```php
class FormPluginsIntegration implements IntegrationInterface {
    
    public function detectFormPlugins(): array {
        // Check for CF7, WPForms, Gravity Forms, Ninja Forms
    }
    
    public function getFormFields(): array {
        // Extract field types and names
    }
    
    public function mapFieldsToDataCategories(): array {
        // Map form fields to GDPR data categories
    }
}
```

### Analytics Tracking
```php
class AnalyticsIntegration implements IntegrationInterface {
    
    public function detectAnalyticsTools(): array {
        // Detect GA4, Matomo, etc. from site
    }
    
    public function getTrackedDataTypes(): array {
        // List of data being tracked
    }
}
```

---

## 🎨 ADMIN TEMPLATES

### Questionnaire Interface (`admin/questionnaire.php`)
```php
<div class="complyflow-questionnaire">
    <progress-bar :percentage="progressPercentage"></progress-bar>
    
    <div v-for="question in currentQuestions" :key="question.id">
        <question-component
            :question="question"
            v-model="responses[question.id]"
            @change="onQuestionChange"
        ></question-component>
    </div>
    
    <button @click="previousQuestion">← Previous</button>
    <button @click="nextQuestion" :disabled="!isCurrentSectionValid">
        Next →
    </button>
    <button @click="skipSection">Skip Section</button>
</div>

<script>
// Alpine.js / Vue.js driven
// Smart conditional logic
// Auto-save responses
// Progress tracking
</script>
```

### Visual Editor (`admin/customization-editor.php`)
```php
<div class="complyflow-visual-editor">
    <div class="editor-sidebar">
        <section-tree 
            :sections="document.sections"
            @reorder="reorderSections"
        ></section-tree>
    </div>
    
    <div class="editor-main">
        <section-editor
            v-for="section in document.sections"
            :section="section"
            @update="updateSection"
            @delete="deleteSection"
        ></section-editor>
    </div>
    
    <div class="editor-preview">
        <!-- Live preview of document -->
    </div>
</div>
```

### Code Editor (`admin/code-editor.php`)
```php
<div class="complyflow-code-editor">
    <textarea
        id="legal-code-editor"
        data-language="html"
    ><?php echo esc_textarea($document->content); ?></textarea>
    
    <div class="variable-reference">
        <h3>Available Variables</h3>
        <ul>
            <li>{{business_name}}</li>
            <li>{{contact_email}}</li>
            <li>{{jurisdiction}}</li>
            <!-- etc -->
        </ul>
    </div>
</div>

<script>
// CodeMirror integration
// Syntax highlighting
// Variable autocomplete
// Preview on change
</script>
```

### Compliance Report (`admin/compliance-report.php`)
```php
<div class="complyflow-compliance-report">
    <div class="compliance-score">
        <h2><?php echo $report->score; ?>/100</h2>
        <p><?php echo $report->summary; ?></p>
    </div>
    
    <div class="issues-list">
        <issue-item
            v-for="issue in reportIssues"
            :issue="issue"
            :key="issue.id"
        ></issue-item>
    </div>
    
    <button @click="exportReportPDF">Export PDF</button>
</div>
```

---

## 🔒 SECURITY CONSIDERATIONS

### Input Validation
- Sanitize all user input (text fields, questionnaire answers)
- Validate document type against whitelist
- Validate jurisdiction against list of supported regions
- Validate language codes

### Output Escaping
- Escape all document content when displaying (wp_kses_post)
- Escape HTML attributes (esc_attr)
- Escape URLs (esc_url)
- Raw HTML output only in admin editor (current_user_can check)

### Access Control
- Require `manage_options` capability for document management
- Check capabilities in REST endpoints
- Audit logging of all edits
- Version history prevents unauthorized changes

### Data Protection
- Encrypt sensitive business data (optional)
- Sanitize AI-generated content
- No data collection without consent
- GDPR-compliant data retention policies

---

## 🧪 TESTING STRATEGY

### Unit Tests
- DocumentGenerationService::generateDocument()
- QuestionnaireService::validateResponses()
- RegulatoryService::getApplicableRegulations()
- CustomizationService::replaceVariables()

### Integration Tests
- WooCommerce auto-detection
- Form plugin integration
- Publishing to WordPress page
- Shortcode rendering

### End-to-End Tests
- Complete document generation workflow
- Edit → Preview → Publish flow
- Version control functionality
- PDF export

### Performance Tests
- Document generation time (target: <5 seconds)
- Database query optimization
- Large document rendering (50,000+ words)
- Search performance (1000+ documents)

---

## 📋 HOOKS & FILTERS

### Actions
```php
// Document lifecycle
do_action('complyflow_document_before_generate', $documentType, $responses);
do_action('complyflow_document_generated', $document);
do_action('complyflow_document_before_publish', $document);
do_action('complyflow_document_published', $document, $pageId);
do_action('complyflow_document_before_delete', $document);

// Customization
do_action('complyflow_section_updated', $document, $sectionKey, $oldContent, $newContent);
do_action('complyflow_custom_section_added', $document, $section);
```

### Filters
```php
// Content modification
apply_filters('complyflow_questionnaire_questions', $questions, $documentType, $jurisdiction);
apply_filters('complyflow_generated_content', $content, $documentType, $responses);
apply_filters('complyflow_section_content', $content, $section, $document);
apply_filters('complyflow_published_content', $content, $document);

// Validation
apply_filters('complyflow_compliance_rules', $rules, $jurisdiction, $documentType);
apply_filters('complyflow_validation_result', $result, $document);

// Integration
apply_filters('complyflow_woocommerce_data', $data, $store);
apply_filters('complyflow_detected_integrations', $integrations);
```

---

## 🚀 PERFORMANCE OPTIMIZATION

### Caching Strategy
- Cache questionnaire definitions (24 hours)
- Cache regulatory data (weekly auto-refresh)
- Cache template library (on-demand invalidation)
- Object cache for frequently-accessed documents
- Transients for expensive calculations

### Database Optimization
- Index on `document_type`, `status`, `jurisdiction`
- Full-text search indexes on title/description
- Archive old versions (move to separate history table)
- Pagination (50 documents per page)

### Frontend Optimization
- Lazy-load editor components
- Minify JavaScript/CSS
- Defer non-critical assets
- Implement virtual scrolling for large document lists
- Code-split editors (questionnaire vs. visual vs. code)

---

## 📈 MONITORING & LOGGING

### Events to Log
- Document created/updated/published/deleted
- Questionnaire responses saved
- Compliance validation performed
- Publishing action completed
- Regulatory update detected
- AI content generated
- Export/PDF generated

### Logging Format
```php
Logger::info('Document published', [
    'document_id' => $documentId,
    'document_type' => 'privacy-policy',
    'jurisdiction' => 'US',
    'user_id' => $userId,
    'page_id' => $pageId,
    'timestamp' => current_time('mysql')
]);
```

### Metrics to Track
- Average document generation time
- Compliance score distribution
- Most common missing clauses
- Regulatory updates frequency
- User editing patterns
- Export/PDF generation volume

---

**Status:** Architecture Complete - Ready for Development Sprint Planning  
**Next Step:** Create individual service unit tests  
**Estimated Development Time:** 8-10 weeks (MVP with 8 core documents)
