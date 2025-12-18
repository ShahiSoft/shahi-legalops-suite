<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes;

if (!defined('ABSPATH')) {
    exit;
}

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\Fixers\{
    MissingAltTextFixer,
    EmptyAltTextFixer,
    RedundantAltTextFixer,
    DecorativeImageFixer,
    MissingH1Fixer,
    MultipleH1Fixer,
    EmptyHeadingFixer,
    EmptyLinkFixer,
    GenericLinkTextFixer,
    NewWindowLinkFixer,
    DownloadLinkFixer,
    ExternalLinkFixer,
    LinkDestinationFixer,
    SkipLinkFixer,
    MissingFormLabelFixer,
    FieldsetLegendFixer,
    RequiredAttributeFixer,
    ErrorMessageFixer,
    AutocompleteFixer,
    InputTypeFixer,
    PlaceholderLabelFixer,
    CustomControlFixer,
    ButtonLabelFixer,
    OrphanedLabelFixer,
    FormAriaFixer,
    SkippedHeadingLevelFixer,
    HeadingNestingFixer,
    HeadingLengthFixer,
    HeadingUniquenessFixer,
    HeadingVisualFixer,
    TableHeaderFixer,
    TableCaptionFixer,
    ComplexTableFixer,
    LayoutTableFixer,
    EmptyTableCellFixer,
    ImageMapAltFixer,
    IframeTitleFixer,
    SvgAccessibilityFixer,
    ComplexImageFixer,
    LogoImageFixer,
    BackgroundImageFixer,
    AltTextQualityFixer,
    PositiveTabIndexFixer,
    InteractiveElementFixer,
    ModalAccessibilityFixer,
    FocusIndicatorFixer,
    KeyboardTrapFixer,
    FocusOrderFixer,
    TextColorContrastFixer,
    ColorRelianceFixer,
    ComplexContrastFixer,
    TouchTargetFixer,
    TouchGestureFixer,
    ViewportFixer,
    AriaRoleFixer,
    AriaAttributeFixer,
    AriaStateFixer,
    LandmarkRoleFixer,
    RedundantAriaFixer,
    InvalidAriaCombinationFixer,
    HiddenContentFixer,
    SemanticHtmlFixer,
    LiveRegionFixer,
    PageStructureFixer,
    VideoAccessibilityFixer,
    AudioAccessibilityFixer,
    MediaAlternativeFixer
};

/**
 * Fixer Registry
 * Maps checker IDs to their corresponding fixer classes
 */
class FixerRegistry {
    private static $registry = [];
    private static $initialized = false;

    /**
     * Initialize the fixer registry
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$registry = [
            // Image and Link Fixers
            'missing-alt-text' => MissingAltTextFixer::class,
            'empty-alt-text' => EmptyAltTextFixer::class,
            'redundant-alt-text' => RedundantAltTextFixer::class,
            'decorative-image' => DecorativeImageFixer::class,
            'missing-h1' => MissingH1Fixer::class,
            'multiple-h1' => MultipleH1Fixer::class,
            'empty-heading' => EmptyHeadingFixer::class,
            'empty-link' => EmptyLinkFixer::class,
            'generic-link-text' => GenericLinkTextFixer::class,
            'new-window-link' => NewWindowLinkFixer::class,
            'download-link' => DownloadLinkFixer::class,
            'external-link' => ExternalLinkFixer::class,
            'link-destination' => LinkDestinationFixer::class,
            'skip-link' => SkipLinkFixer::class,

            // Form Fixers
            'missing-form-label' => MissingFormLabelFixer::class,
            'fieldset-legend' => FieldsetLegendFixer::class,
            'required-attribute' => RequiredAttributeFixer::class,
            'error-message' => ErrorMessageFixer::class,
            'autocomplete' => AutocompleteFixer::class,
            'input-type' => InputTypeFixer::class,
            'placeholder-label' => PlaceholderLabelFixer::class,
            'custom-control' => CustomControlFixer::class,
            'button-label' => ButtonLabelFixer::class,
            'orphaned-label' => OrphanedLabelFixer::class,
            'form-aria' => FormAriaFixer::class,

            // Heading Fixers
            'skipped-heading-level' => SkippedHeadingLevelFixer::class,
            'heading-nesting' => HeadingNestingFixer::class,
            'heading-length' => HeadingLengthFixer::class,
            'heading-uniqueness' => HeadingUniquenessFixer::class,
            'heading-visual' => HeadingVisualFixer::class,

            // Content Fixers
            'table-header' => TableHeaderFixer::class,
            'table-caption' => TableCaptionFixer::class,
            'complex-table' => ComplexTableFixer::class,
            'layout-table' => LayoutTableFixer::class,
            'empty-table-cell' => EmptyTableCellFixer::class,
            'image-map-alt' => ImageMapAltFixer::class,
            'iframe-title' => IframeTitleFixer::class,
            'svg-accessibility' => SvgAccessibilityFixer::class,
            'complex-image' => ComplexImageFixer::class,
            'logo-image' => LogoImageFixer::class,
            'background-image' => BackgroundImageFixer::class,
            'alt-text-quality' => AltTextQualityFixer::class,

            // Interactivity Fixers
            'positive-tabindex' => PositiveTabIndexFixer::class,
            'interactive-element' => InteractiveElementFixer::class,
            'modal-accessibility' => ModalAccessibilityFixer::class,
            'focus-indicator' => FocusIndicatorFixer::class,
            'keyboard-trap' => KeyboardTrapFixer::class,
            'focus-order' => FocusOrderFixer::class,
            'text-color-contrast' => TextColorContrastFixer::class,
            'color-reliance' => ColorRelianceFixer::class,
            'complex-contrast' => ComplexContrastFixer::class,
            'touch-target' => TouchTargetFixer::class,
            'touch-gesture' => TouchGestureFixer::class,
            'viewport' => ViewportFixer::class,

            // ARIA and Semantic Fixers
            'aria-role' => AriaRoleFixer::class,
            'aria-attribute' => AriaAttributeFixer::class,
            'aria-state' => AriaStateFixer::class,
            'landmark-role' => LandmarkRoleFixer::class,
            'redundant-aria' => RedundantAriaFixer::class,
            'invalid-aria-combination' => InvalidAriaCombinationFixer::class,
            'hidden-content' => HiddenContentFixer::class,
            'semantic-html' => SemanticHtmlFixer::class,
            'live-region' => LiveRegionFixer::class,
            'page-structure' => PageStructureFixer::class,

            // Media Fixers
            'video-accessibility' => VideoAccessibilityFixer::class,
            'audio-accessibility' => AudioAccessibilityFixer::class,
            'media-alternative' => MediaAlternativeFixer::class,
        ];

        self::$initialized = true;
    }

    /**
     * Get fixer class for a given checker ID
     *
     * @param string $checker_id
     * @return string|null
     */
    public static function get_fixer_class($checker_id) {
        self::init();
        return isset(self::$registry[$checker_id]) ? self::$registry[$checker_id] : null;
    }

    /**
     * Get fixer instance for a given checker ID
     *
     * @param string $checker_id
     * @return object|null
     */
    public static function get_fixer($checker_id) {
        $class = self::get_fixer_class($checker_id);
        if ($class && class_exists($class)) {
            return new $class();
        }
        return null;
    }

    /**
     * Check if fixer exists for checker ID
     *
     * @param string $checker_id
     * @return bool
     */
    public static function has_fixer($checker_id) {
        return self::get_fixer_class($checker_id) !== null;
    }

    /**
     * Get all registered fixer IDs
     *
     * @return array
     */
    public static function get_all_fixer_ids() {
        self::init();
        return array_keys(self::$registry);
    }

    /**
     * Get fixer count
     *
     * @return int
     */
    public static function get_fixer_count() {
        self::init();
        return count(self::$registry);
    }
}
