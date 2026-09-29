<?php

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Element_Base;

class WR_Elementor_Animations_Controls
{
    private const ORDER_DELAY_STEP = 150;

    private const ANIMATION_TYPES = [
        'none' => 'None',
        'fade' => 'Fade',
        'fade-up' => 'Fade Up',
        'fade-down' => 'Fade Down',
        'fade-left' => 'Fade Left',
        'fade-right' => 'Fade Right',
        'slide-up' => 'Slide Up',
        'slide-down' => 'Slide Down',
        'slide-left' => 'Slide Left',
        'slide-right' => 'Slide Right',
        'scale-in' => 'Scale In',
        'scale-out' => 'Scale Out',
    ];

    private const TRANSFORM_TYPES = [
        'none' => 'None',
        'rotate' => 'Rotate',
        'scale' => 'Scale',
        'rotate-scale' => 'Rotate + Scale',
    ];

    public function init(): void
    {
        // Register the WR Animation controls on existing Elementor element stacks.
        add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'register_controls'], 10, 2);

        // Add classes, CSS custom properties, and data attributes before Elementor renders the wrapper.
        add_action('elementor/frontend/before_render', [$this, 'add_render_attributes']);

        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function register_controls($element): void
    {
        if (!is_object($element) || !method_exists($element, 'start_controls_section')) {
            return;
        }

        if (method_exists($element, 'get_controls')) {
            $controls = $element->get_controls();

            if (isset($controls['wr_animation_enable'])) {
                return;
            }
        }

        $element->start_controls_section(
            'wr_animation_section',
            [
                'label' => esc_html__('Glenmark Animation', 'webrevolution-elementor-animations'),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->add_control(
            'wr_animation_enable',
            [
                'label' => esc_html__('Enable animation', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'webrevolution-elementor-animations'),
                'label_off' => esc_html__('No', 'webrevolution-elementor-animations'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $element->add_control(
            'wr_animation_type',
            [
                'label' => esc_html__('Animation type', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => $this->get_animation_type_options(),
                'condition' => [
                    'wr_animation_enable' => 'yes',
                ],
            ]
        );

        $element->add_responsive_control(
            'wr_animation_duration',
            [
                'label' => esc_html__('Duration', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 700,
                'min' => 100,
                'max' => 5000,
                'step' => 50,
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-animation-duration: {{VALUE}}ms;',
                ],
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->add_responsive_control(
            'wr_animation_delay',
            [
                'label' => esc_html__('Delay', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 5000,
                'step' => 50,
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-animation-delay: {{VALUE}}ms;',
                ],
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->add_responsive_control(
            'wr_animation_distance',
            [
                'label' => esc_html__('Distance', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 0,
                'max' => 1000,
                'step' => 1,
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-animation-distance: {{VALUE}}px; --wr-animation-distance-negative: -{{VALUE}}px;',
                ],
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type' => [
                        'fade-up',
                        'fade-down',
                        'fade-left',
                        'fade-right',
                        'slide-up',
                        'slide-down',
                        'slide-left',
                        'slide-right',
                    ],
                ],
            ]
        );

        $element->add_control(
            'wr_animation_initial_scale',
            [
                'label' => esc_html__('Initial scale', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'min' => 0.5,
                'max' => 1.5,
                'step' => 0.05,
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type' => ['scale-in', 'scale-out'],
                ],
            ]
        );

        $element->add_control(
            'wr_animation_easing',
            [
                'label' => esc_html__('Easing', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease-out',
                'options' => [
                    'ease' => esc_html__('ease', 'webrevolution-elementor-animations'),
                    'ease-in' => esc_html__('ease-in', 'webrevolution-elementor-animations'),
                    'ease-out' => esc_html__('ease-out', 'webrevolution-elementor-animations'),
                    'ease-in-out' => esc_html__('ease-in-out', 'webrevolution-elementor-animations'),
                    'linear' => esc_html__('linear', 'webrevolution-elementor-animations'),
                    'custom' => esc_html__('custom cubic-bezier', 'webrevolution-elementor-animations'),
                ],
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->add_control(
            'wr_animation_custom_easing',
            [
                'label' => esc_html__('Custom cubic-bezier', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'cubic-bezier(0.22, 1, 0.36, 1)',
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                    'wr_animation_easing' => 'custom',
                ],
            ]
        );

        $element->add_control(
            'wr_animation_trigger',
            [
                'label' => esc_html__('Trigger', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SELECT,
                'default' => 'viewport',
                'options' => [
                    'load' => esc_html__('On page load', 'webrevolution-elementor-animations'),
                    'viewport' => esc_html__('On enter viewport', 'webrevolution-elementor-animations'),
                ],
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->add_control(
            'wr_animation_threshold',
            [
                'label' => esc_html__('Viewport threshold', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0.15,
                'min' => 0,
                'max' => 1,
                'step' => 0.05,
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                    'wr_animation_trigger' => 'viewport',
                ],
            ]
        );

        $element->add_control(
            'wr_animation_once',
            [
                'label' => esc_html__('Trigger once', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'webrevolution-elementor-animations'),
                'label_off' => esc_html__('No', 'webrevolution-elementor-animations'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->add_control(
            'wr_animation_order',
            [
                'label' => esc_html__('Animation order', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 20,
                'step' => 1,
                'condition' => [
                    'wr_animation_enable' => 'yes',
                    'wr_animation_type!' => 'none',
                ],
            ]
        );

        $element->end_controls_section();

        $element->start_controls_section(
            'wr_transform_section',
            [
                'label' => esc_html__('Glenmark Transform', 'webrevolution-elementor-animations'),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->add_control(
            'wr_transform_enable',
            [
                'label' => esc_html__('Enable transform', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'webrevolution-elementor-animations'),
                'label_off' => esc_html__('No', 'webrevolution-elementor-animations'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $element->add_control(
            'wr_transform_type',
            [
                'label' => esc_html__('Hover transform', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => $this->get_transform_type_options(),
                'condition' => [
                    'wr_transform_enable' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'wr_transform_rotate_degrees',
            [
                'label' => esc_html__('Rotate degrees', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 360,
                'min' => -1080,
                'max' => 1080,
                'step' => 15,
                'condition' => [
                    'wr_transform_enable' => 'yes',
                    'wr_transform_type' => ['rotate', 'rotate-scale'],
                ],
            ]
        );

        $element->add_control(
            'wr_transform_scale',
            [
                'label' => esc_html__('Scale', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 1.05,
                'min' => 0.1,
                'max' => 3,
                'step' => 0.01,
                'condition' => [
                    'wr_transform_enable' => 'yes',
                    'wr_transform_type' => ['scale', 'rotate-scale'],
                ],
            ]
        );

        $element->add_responsive_control(
            'wr_transform_button_hover_padding',
            [
                'label' => esc_html__('Button hover padding', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-transform-button-padding-top: {{TOP}}{{UNIT}}; --wr-transform-button-padding-right: {{RIGHT}}{{UNIT}}; --wr-transform-button-padding-bottom: {{BOTTOM}}{{UNIT}}; --wr-transform-button-padding-left: {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'wr_transform_enable' => 'yes',
                ],
            ]
        );

        $element->add_responsive_control(
            'wr_transform_duration',
            [
                'label' => esc_html__('Duration', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::NUMBER,
                'default' => 300,
                'min' => 0,
                'max' => 5000,
                'step' => 50,
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-transform-duration: {{VALUE}}ms;',
                ],
                'condition' => [
                    'wr_transform_enable' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'wr_transform_easing',
            [
                'label' => esc_html__('Easing', 'webrevolution-elementor-animations'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease',
                'options' => [
                    'ease' => esc_html__('ease', 'webrevolution-elementor-animations'),
                    'ease-in' => esc_html__('ease-in', 'webrevolution-elementor-animations'),
                    'ease-out' => esc_html__('ease-out', 'webrevolution-elementor-animations'),
                    'ease-in-out' => esc_html__('ease-in-out', 'webrevolution-elementor-animations'),
                    'linear' => esc_html__('linear', 'webrevolution-elementor-animations'),
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--wr-transform-easing: {{VALUE}};',
                ],
                'condition' => [
                    'wr_transform_enable' => 'yes',
                ],
            ]
        );

        $element->end_controls_section();
    }

    public function add_render_attributes(Element_Base $element): void
    {
        $settings = $element->get_settings_for_display();

        $this->add_animation_render_attributes($element, $settings);
        $this->add_transform_render_attributes($element, $settings);
    }

    private function add_animation_render_attributes(Element_Base $element, array $settings): void
    {

        if (empty($settings['wr_animation_enable']) || 'yes' !== $settings['wr_animation_enable']) {
            return;
        }

        $animation_type = $this->sanitize_animation_type($settings['wr_animation_type'] ?? 'none');

        if ('none' === $animation_type) {
            return;
        }

        $trigger = $this->sanitize_trigger($settings['wr_animation_trigger'] ?? 'viewport');
        $threshold = $this->sanitize_float($settings['wr_animation_threshold'] ?? 0.15, 0, 1, 0.15);
        $trigger_once = !empty($settings['wr_animation_once']) && 'yes' === $settings['wr_animation_once'];
        $animation_order = $this->sanitize_int($settings['wr_animation_order'] ?? 0, 0, 20, 0);
        // Animation order adds a small constant step on top of the manually configured delay.
        $order_delay = $animation_order * self::ORDER_DELAY_STEP;
        $easing = $this->sanitize_easing($settings);
        $initial_scale = $this->sanitize_initial_scale($settings['wr_animation_initial_scale'] ?? '', $animation_type);

        $styles = [
            '--wr-animation-easing: ' . $easing,
            '--wr-animation-order-delay: ' . $order_delay . 'ms',
            '--wr-animation-order-step: ' . self::ORDER_DELAY_STEP . 'ms',
        ];

        if (null !== $initial_scale) {
            $styles[] = '--wr-animation-initial-scale: ' . $initial_scale;
        }

        $element->add_render_attribute('_wrapper', [
            'class' => [
                'wr-animate',
                'wr-animation-' . $animation_type,
            ],
            'style' => implode('; ', array_map('esc_attr', $styles)) . ';',
            'data-wr-animation-trigger' => esc_attr($trigger),
            'data-wr-animation-threshold' => esc_attr((string) $threshold),
            'data-wr-animation-once' => $trigger_once ? 'yes' : 'no',
        ]);
    }

    private function add_transform_render_attributes(Element_Base $element, array $settings): void
    {
        if (empty($settings['wr_transform_enable']) || 'yes' !== $settings['wr_transform_enable']) {
            return;
        }

        $transform_type = $this->sanitize_transform_type($settings['wr_transform_type'] ?? 'none');
        $styles = [];

        if (in_array($transform_type, ['rotate', 'rotate-scale'], true)) {
            $rotation = $this->sanitize_float($settings['wr_transform_rotate_degrees'] ?? 360, -1080, 1080, 360);
            $styles[] = '--wr-transform-hover-rotate: ' . $rotation . 'deg';
        }

        if (in_array($transform_type, ['scale', 'rotate-scale'], true)) {
            $scale = $this->sanitize_float($settings['wr_transform_scale'] ?? 1.05, 0.1, 3, 1.05);
            $styles[] = '--wr-transform-hover-scale: ' . $scale;
        }

        $render_attributes = [
            'class' => [
                'wr-transform',
                'wr-transform-' . $transform_type,
            ],
        ];

        if (!empty($styles)) {
            $render_attributes['style'] = implode('; ', array_map('esc_attr', $styles)) . ';';
        }

        $element->add_render_attribute('_wrapper', $render_attributes);
    }

    public function enqueue_styles(): void
    {
        $path = WR_ELEMENTOR_ANIMATIONS_PATH . 'assets/css/animations.css';
        $version = file_exists($path) ? (string) filemtime($path) : WR_ELEMENTOR_ANIMATIONS_VERSION;

        wp_enqueue_style(
            'wr-elementor-animations',
            WR_ELEMENTOR_ANIMATIONS_URL . 'assets/css/animations.css',
            [],
            $version
        );
    }

    public function enqueue_scripts(): void
    {
        $path = WR_ELEMENTOR_ANIMATIONS_PATH . 'assets/js/animations.js';
        $version = file_exists($path) ? (string) filemtime($path) : WR_ELEMENTOR_ANIMATIONS_VERSION;
        $dependencies = wp_script_is('elementor-frontend', 'registered') ? ['elementor-frontend'] : [];

        wp_enqueue_script(
            'wr-elementor-animations',
            WR_ELEMENTOR_ANIMATIONS_URL . 'assets/js/animations.js',
            $dependencies,
            $version,
            true
        );
    }

    private function get_animation_type_options(): array
    {
        $options = [];

        foreach (self::ANIMATION_TYPES as $value => $label) {
            $options[$value] = esc_html__($label, 'webrevolution-elementor-animations');
        }

        return $options;
    }

    private function get_transform_type_options(): array
    {
        $options = [];

        foreach (self::TRANSFORM_TYPES as $value => $label) {
            $options[$value] = esc_html__($label, 'webrevolution-elementor-animations');
        }

        return $options;
    }

    private function sanitize_animation_type(string $animation_type): string
    {
        return array_key_exists($animation_type, self::ANIMATION_TYPES) ? $animation_type : 'none';
    }

    private function sanitize_transform_type(string $transform_type): string
    {
        return array_key_exists($transform_type, self::TRANSFORM_TYPES) ? $transform_type : 'none';
    }

    private function sanitize_trigger(string $trigger): string
    {
        return in_array($trigger, ['load', 'viewport'], true) ? $trigger : 'viewport';
    }

    private function sanitize_easing(array $settings): string
    {
        $easing = sanitize_text_field((string) ($settings['wr_animation_easing'] ?? 'ease-out'));
        $allowed = ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'];

        if (in_array($easing, $allowed, true)) {
            return $easing;
        }

        if ('custom' !== $easing) {
            return 'ease-out';
        }

        $custom_easing = sanitize_text_field((string) ($settings['wr_animation_custom_easing'] ?? ''));

        if (preg_match('/^cubic-bezier\(\s*-?\d*\.?\d+\s*,\s*-?\d*\.?\d+\s*,\s*-?\d*\.?\d+\s*,\s*-?\d*\.?\d+\s*\)$/', $custom_easing)) {
            return $custom_easing;
        }

        return 'ease-out';
    }

    private function sanitize_initial_scale($value, string $animation_type): ?string
    {
        if ('' === $value || null === $value) {
            if ('scale-in' === $animation_type) {
                return '0.85';
            }

            if ('scale-out' === $animation_type) {
                return '1.15';
            }

            return null;
        }

        $scale = $this->sanitize_float($value, 0.5, 1.5, 'scale-out' === $animation_type ? 1.15 : 0.85);

        return rtrim(rtrim(number_format($scale, 2, '.', ''), '0'), '.');
    }

    private function sanitize_int($value, int $min, int $max, int $default): int
    {
        $value = is_numeric($value) ? (int) $value : $default;

        return max($min, min($max, $value));
    }

    private function sanitize_float($value, float $min, float $max, float $default): float
    {
        $value = is_numeric($value) ? (float) $value : $default;

        return max($min, min($max, $value));
    }
}