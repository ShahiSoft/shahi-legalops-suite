# SLOS Accessibility Scanner - Phased Strategic Plan

## Phase 1: Foundation & Core Scanning (MVP)
**Goal:** Establish the scanning infrastructure and implement critical checks to provide immediate value.

### 1.1 Module Infrastructure
- [ ] Create `SLOS_Accessibility_Scanner` class structure.
- [ ] Register admin menu and settings page.
- [ ] Create database tables for storing scan results (if needed) or use post meta.

### 1.2 Scanning Engine (Server-Side)
- [ ] Implement `Scanner_Engine` class.
- [ ] Create abstract `Check` class interface.
- [ ] Implement `Check_Runner` to execute registered checks.
- [ ] Hook into `save_post` to trigger scans automatically.

### 1.3 Initial Critical Checks (The "First 10")
**Image & Media:**
- [ ] Check 1: Missing `alt` text on images.
- [ ] Check 2: Empty `alt` text on non-decorative images (heuristic).
**Headings:**
- [ ] Check 3: Missing H1 heading.
- [ ] Check 4: Skipped heading levels (e.g., H2 to H4).
**Links:**
- [ ] Check 5: Empty links or links with no text.
- [ ] Check 6: "Click here" generic link text detection.

### 1.4 Basic Reporting
- [ ] Add "Accessibility Status" meta box to the Post Editor.
- [ ] Display list of errors/warnings in the meta box.
- [ ] Simple "Scan Now" button in the meta box.

---

## Phase 2: Enhanced Scanning & Frontend Widget
**Goal:** Expand coverage and add user-facing accessibility tools.

### 2.1 Expanded Checks (30+ Total)
- [ ] Implement remaining Image, Heading, and Link checks.
- [ ] Add Form accessibility checks (labels, inputs).
- [ ] Add Color Contrast checks (requires some JS/CSS analysis).

### 2.2 Frontend Accessibility Widget
- [ ] Create a floating accessibility toolbar.
- [ ] Features: Text Resize, High Contrast Mode, Grayscale.
- [ ] Admin settings to customize widget appearance.

### 2.3 Bulk Scanning
- [ ] Create a dedicated "Accessibility Scanner" dashboard page.
- [ ] Implement AJAX-based bulk scanner for all existing posts.
- [ ] Summary report of site-wide health.

---

## Phase 3: Remediation & AI Integration
**Goal:** Help users fix issues, not just find them.

### 3.1 Guided & Auto-Fixes
- [ ] "Fix Now" button for simple issues (e.g., strip title attribute).
- [ ] UI to easily input missing Alt text directly from the report.

### 3.2 AI Enhancements
- [ ] Integrate with OpenAI/local LLM for Alt Text generation.
- [ ] AI suggestions for readability improvements.

### 3.3 Advanced Reporting
- [ ] PDF Report generation.
- [ ] Historical tracking of accessibility score.

---

## Phase 4: Enterprise & Compliance
**Goal:** Full compliance features for large organizations.

### 4.1 Compliance Standards
- [ ] Map checks to specific WCAG 2.1/2.2 success criteria.
- [ ] Generate "Accessibility Statement" page.

### 4.2 Advanced Features
- [ ] Role-based access control for scanner settings.
- [ ] White-labeling options.
- [ ] Third-party plugin compatibility checks.
