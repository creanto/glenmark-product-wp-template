<?php

/**
 * Scope Elementor "Site Settings → Theme Style" CSS to the site content wrapper.
 *
 * Elementor registers the Theme Style controls on the Kit document
 * (Elementor\Core\Kits\Documents\Kit) through tab classes in
 * core/kits/documents/tabs/*.php. Every control there declares plain
 * `selectors` / group-control `selector` args built from the `{{WRAPPER}}`
 * placeholder, e.g. `{{WRAPPER}} button, {{WRAPPER}} input[type="button"]`.
 * When the kit CSS file is generated, `{{WRAPPER}}` is replaced by the kit
 * wrapper selector (`.elementor-kit-XX`), which lives on <body> — so the rules
 * hit every button on the page, including third-party plugin UI.
 *
 * Here we rewrite the registered selectors right after each Theme Style section
 * is closed, turning `{{WRAPPER}} button` into `{{WRAPPER}} .site-content button`.
 * The kit root stays intact, the Elementor UI is untouched and both the frontend
 * CSS file and the editor preview use the modified selectors.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wrapper the Theme Style rules are limited to. Change in one place.
 */
function webrev_elementor_theme_style_scope()
{
    return (string) apply_filters('wr-pharma-product/elementor/theme_style_scope', '.site-content');
}

/**
 * Theme Style sections to scope.
 *
 * Value = whether the bare `{{WRAPPER}}` selector (the kit root itself, used by
 * Body text color / Body typography) should be scoped as well. Kept false by
 * default so the base font/color stays inheritable for header, footer and
 * anything else outside the content wrapper.
 */
function webrev_elementor_theme_style_scoped_sections()
{
    return (array) apply_filters('wr-pharma-product/elementor/theme_style_scoped_sections', [
        'section_typography' => false, // Body typography, links, H1-H6
        'section_buttons' => true,
        'section_form_fields' => true,
        'section_images' => true,
    ]);
}

/**
 * Prefix a single Elementor selector string with the scope wrapper.
 */
function webrev_elementor_scope_selector_string($selector, $scope, $scope_root)
{
    $placeholder = '{{WRAPPER}}';
    $parts = explode(',', $selector);

    foreach ($parts as $index => $part) {
        $part = trim($part);

        if ('' === $part || 0 !== strpos($part, $placeholder)) {
            $parts[$index] = $part;
            continue;
        }

        $rest = trim(substr($part, strlen($placeholder)));

        if ('' === $rest) {
            $parts[$index] = $scope_root ? $placeholder . ' ' . $scope : $part;
            continue;
        }

        $parts[$index] = $placeholder . ' ' . $scope . ' ' . $rest;
    }

    return implode(',', array_filter($parts, 'strlen'));
}

/**
 * Rewrite the selectors of every control belonging to a Theme Style section.
 *
 * @param \Elementor\Controls_Stack $element
 * @param string                    $section_id
 */
function webrev_elementor_scope_theme_style_section($element, $section_id, $args)
{
    if (!$element instanceof \Elementor\Core\Kits\Documents\Kit) {
        return;
    }

    $sections = webrev_elementor_theme_style_scoped_sections();

    if (!array_key_exists($section_id, $sections)) {
        return;
    }

    $scope = trim(webrev_elementor_theme_style_scope());

    if ('' === $scope) {
        return;
    }

    $stack = \Elementor\Plugin::$instance->controls_manager->get_stacks($element->get_unique_name());

    if (empty($stack)) {
        return;
    }

    // Style controls may live in a separate bucket when Elementor's optimized-controls mode is on.
    $controls = array_merge(
        isset($stack['controls']) ? $stack['controls'] : [],
        isset($stack['style_controls']) ? $stack['style_controls'] : []
    );

    $scope_root = !empty($sections[$section_id]);

    foreach ($controls as $control_id => $control) {
        if (empty($control['selectors']) || !is_array($control['selectors'])) {
            continue;
        }

        if (!isset($control['section']) || $section_id !== $control['section']) {
            continue;
        }

        $scoped_selectors = [];
        $has_changed = false;

        foreach ($control['selectors'] as $selector => $css) {
            $scoped_selector = webrev_elementor_scope_selector_string($selector, $scope, $scope_root);

            if ($scoped_selector !== $selector) {
                $has_changed = true;
            }

            $scoped_selectors[$scoped_selector] = $css;
        }

        if ($has_changed) {
            $element->update_control($control_id, ['selectors' => $scoped_selectors]);
        }
    }
}

add_action('elementor/init', function () {
    if (!did_action('elementor/loaded')) {
        return;
    }

    // Fired by Controls_Stack::end_controls_section(), so all controls of the section are registered.
    add_action('elementor/element/after_section_end', 'webrev_elementor_scope_theme_style_section', 10, 3);
});
