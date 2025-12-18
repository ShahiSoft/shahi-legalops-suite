<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Missing Alt Text Fixer
 */
class MissingAltTextFixer extends BaseFixer {
    public function get_id() { return 'missing-alt-text'; }
    public function get_description() { return 'Add alt text to images'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $images = $dom->getElementsByTagName('img');
        $fixed_count = 0;
        
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                $src = $img->getAttribute('src');
                $alt_text = $this->generate_alt_text($src);
                $img->setAttribute('alt', $alt_text);
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Empty Alt Text Fixer
 */
class EmptyAltTextFixer extends BaseFixer {
    public function get_id() { return 'empty-alt-text'; }
    public function get_description() { return 'Fix empty alt attributes'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $images = $dom->getElementsByTagName('img');
        $fixed_count = 0;
        
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                $src = $img->getAttribute('src');
                $alt_text = $this->generate_alt_text($src);
                $img->setAttribute('alt', $alt_text);
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Redundant Alt Text Fixer
 */
class RedundantAltTextFixer extends BaseFixer {
    public function get_id() { return 'redundant-alt-text'; }
    public function get_description() { return 'Remove redundant alt text'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $images = $dom->getElementsByTagName('img');
        $fixed_count = 0;
        
        foreach ($images as $img) {
            if ($img->hasAttribute('alt')) {
                $alt = trim($img->getAttribute('alt'));
                // Remove "image of", "picture of", "photo of" prefix
                $alt = preg_replace('/^(image|picture|photo) of /i', '', $alt);
                // Remove redundant image/png extensions
                $alt = preg_replace('/\.(jpg|jpeg|png|gif|webp)$/i', '', $alt);
                $img->setAttribute('alt', $alt);
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Decorative Image Fixer
 */
class DecorativeImageFixer extends BaseFixer {
    public function get_id() { return 'decorative-image'; }
    public function get_description() { return 'Mark decorative images with empty alt'; }
    
    public function fix($content) {
        // For decorative images, set alt to empty and add aria-hidden
        $dom = $this->get_dom($content);
        $images = $dom->getElementsByTagName('img');
        $fixed_count = 0;
        
        // Check for common decorative patterns
        foreach ($images as $img) {
            $class = $img->getAttribute('class');
            $src = $img->getAttribute('src');
            
            if (preg_match('/(icon|bullet|spacer|divider|decoration|ornament)/i', $class) ||
                preg_match('/(icon|bullet|spacer|divider|decoration)/i', $src)) {
                $img->setAttribute('alt', '');
                $img->setAttribute('aria-hidden', 'true');
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Missing H1 Fixer
 */
class MissingH1Fixer extends BaseFixer {
    public function get_id() { return 'missing-h1'; }
    public function get_description() { return 'Add H1 to pages without one'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $h1s = $dom->getElementsByTagName('h1');
        
        if ($h1s->length === 0) {
            // Insert H1 at the beginning of content
            $h1 = $dom->createElement('h1');
            $h1->textContent = get_bloginfo('name');
            
            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body && $body->firstChild) {
                $body->insertBefore($h1, $body->firstChild);
            } else {
                $body->appendChild($h1);
            }
            
            return ['fixed_count' => 1, 'content' => $this->dom_to_html($dom)];
        }
        
        return ['fixed_count' => 0, 'content' => $content];
    }
}

/**
 * Multiple H1 Fixer
 */
class MultipleH1Fixer extends BaseFixer {
    public function get_id() { return 'multiple-h1'; }
    public function get_description() { return 'Convert extra H1s to H2s'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $h1s = $dom->getElementsByTagName('h1');
        $fixed_count = 0;
        
        // Keep first H1, convert rest to H2
        $h1_array = [];
        foreach ($h1s as $h1) {
            $h1_array[] = $h1;
        }
        
        for ($i = 1; $i < count($h1_array); $i++) {
            $h2 = $dom->createElement('h2');
            $h2->textContent = $h1_array[$i]->textContent;
            $h1_array[$i]->parentNode->replaceChild($h2, $h1_array[$i]);
            $fixed_count++;
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Empty Heading Fixer
 */
class EmptyHeadingFixer extends BaseFixer {
    public function get_id() { return 'empty-heading'; }
    public function get_description() { return 'Remove empty heading tags'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        $fixed_count = 0;
        
        // Convert to array to avoid iterator issues
        $headings_array = [];
        foreach ($headings as $h) {
            $headings_array[] = $h;
        }
        
        foreach ($headings_array as $heading) {
            $text = trim($heading->textContent);
            if ($text === '') {
                $heading->parentNode->removeChild($heading);
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Empty Link Fixer
 */
class EmptyLinkFixer extends BaseFixer {
    public function get_id() { return 'empty-link'; }
    public function get_description() { return 'Add aria-label to empty links'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        
        foreach ($links as $link) {
            $text = trim($link->textContent);
            $has_aria = $link->hasAttribute('aria-label') && trim($link->getAttribute('aria-label')) !== '';
            
            if ($text === '' && !$has_aria) {
                // Check for images with alt
                $images = $link->getElementsByTagName('img');
                if ($images->length === 0) {
                    $href = $link->getAttribute('href');
                    $label = $href ? basename($href) : 'Link';
                    $link->setAttribute('aria-label', $label);
                    $fixed_count++;
                }
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Generic Link Text Fixer
 */
class GenericLinkTextFixer extends BaseFixer {
    public function get_id() { return 'generic-link-text'; }
    public function get_description() { return 'Improve vague link text'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        $generic_words = ['click here', 'read more', 'learn more', 'more', 'link', 'here'];
        
        foreach ($links as $link) {
            $text = strtolower(trim($link->textContent));
            
            if (in_array($text, $generic_words)) {
                $href = $link->getAttribute('href');
                $page = get_the_title($this->get_post_id_from_url($href));
                $new_text = !empty($page) ? "Read more about $page" : 'Learn more';
                $link->textContent = $new_text;
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
    
    private function get_post_id_from_url($url) {
        $post = url_to_postid($url);
        return $post ?: 0;
    }
}

/**
 * New Window Link Fixer
 */
class NewWindowLinkFixer extends BaseFixer {
    public function get_id() { return 'new-window-link'; }
    public function get_description() { return 'Add warning to links that open in new window'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        
        foreach ($links as $link) {
            if ($link->hasAttribute('target') && $link->getAttribute('target') === '_blank') {
                $text = trim($link->textContent);
                if (strpos($text, '(opens in') === false) {
                    $link->textContent = $text . ' (opens in new window)';
                    $fixed_count++;
                }
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Download Link Fixer
 */
class DownloadLinkFixer extends BaseFixer {
    public function get_id() { return 'download-link'; }
    public function get_description() { return 'Mark download links'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        
        $download_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'ppt', 'pptx'];
        
        foreach ($links as $link) {
            $href = strtolower($link->getAttribute('href'));
            $is_download = false;
            
            foreach ($download_extensions as $ext) {
                if (strpos($href, ".$ext") !== false) {
                    $is_download = true;
                    break;
                }
            }
            
            if ($is_download) {
                $text = trim($link->textContent);
                if (strpos($text, '(') === false) {
                    $ext = strtoupper(pathinfo($href, PATHINFO_EXTENSION));
                    $link->textContent = $text . " ($ext)";
                    $fixed_count++;
                }
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * External Link Fixer
 */
class ExternalLinkFixer extends BaseFixer {
    public function get_id() { return 'external-link'; }
    public function get_description() { return 'Mark external links'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        $home_url = home_url();
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            
            if (!empty($href) && !preg_match('/^(\/|#|mailto:|tel:|$)/', $href) && strpos($href, $home_url) === false) {
                $text = trim($link->textContent);
                if (strpos($text, '(external') === false && !$link->hasAttribute('aria-label')) {
                    $link->setAttribute('aria-label', $text . ' (opens in new window)');
                    $fixed_count++;
                }
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Link Destination Fixer
 */
class LinkDestinationFixer extends BaseFixer {
    public function get_id() { return 'link-destination'; }
    public function get_description() { return 'Ensure links have valid href'; }
    
    public function fix($content) {
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        $fixed_count = 0;
        
        foreach ($links as $link) {
            if (!$link->hasAttribute('href') || trim($link->getAttribute('href')) === '') {
                $link->setAttribute('href', '#');
                $fixed_count++;
            }
        }
        
        return ['fixed_count' => $fixed_count, 'content' => $this->dom_to_html($dom)];
    }
}

/**
 * Skip Link Fixer
 */
class SkipLinkFixer extends BaseFixer {
    public function get_id() { return 'skip-link'; }
    public function get_description() { return 'Add skip to main content link'; }
    
    public function fix($content) {
        // Skip links are better added via hooks, not content manipulation
        // Return 0 as these are site-wide assets
        return ['fixed_count' => 0, 'content' => $content];
    }
}
