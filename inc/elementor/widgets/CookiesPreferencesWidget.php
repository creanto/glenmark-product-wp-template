<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Cookies_Preferences_Widget extends \Elementor\Widget_Button
{
    public function get_name()
    {
        return 'gln-cookies-preferences';
    }

    public function get_title()
    {
        return __('Nastavení cookies', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-button';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'cookies', 'wpconsent', 'souhlas', 'tlačítko'];
    }

    protected function register_controls()
    {
        parent::register_controls();
        $this->remove_control('link');
        $this->update_control('text', [
            'default' => __('Nastavení cookies', 'gln-pharma-product'),
            'placeholder' => __('Nastavení cookies', 'gln-pharma-product'),
        ]);
        $this->add_control('plain_link', [
            'label' => __('Zobrazit jen jako textový odkaz', 'gln-pharma-product'),
            'description' => __('Vykreslí se čistý odkaz bez stylů tlačítka.', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => '',
        ], [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'text',
            ],
        ]);

        // Styly textu ze záložky Styl mají platit i pro režim čistého odkazu,
        // který třídu .elementor-button nemá. Group controls (typografie,
        // stín textu) expandují {{SELECTOR}} do jednotlivých polí už při
        // registraci, proto selektory všech textových polí rozšiřujeme
        // dodatečně o třídu čistého odkazu.
        $this->extend_button_text_style_selectors();
    }

    private function extend_button_text_style_selectors()
    {
        foreach ($this->get_controls() as $control_id => $control) {
            $is_text_style = 0 === strpos($control_id, 'typography_')
                || 0 === strpos($control_id, 'text_shadow_')
                || in_array($control_id, ['button_text_color', 'hover_color'], true);

            if (!$is_text_style || empty($control['selectors']) || !is_array($control['selectors'])) {
                continue;
            }

            $selectors = $control['selectors'];
            foreach ($control['selectors'] as $selector => $css) {
                $plain = str_replace('.elementor-button', '.gln-cookies-preferences-link', $selector);
                if ($plain !== $selector) {
                    $selectors[$plain] = $css;
                }
            }
            $this->update_control($control_id, ['selectors' => $selectors]);
        }
    }

    public function get_settings_for_display($setting_key = null)
    {
        $settings = parent::get_settings_for_display($setting_key);
        if (null !== $setting_key && 'link' !== $setting_key) {
            return $settings;
        }

        // WPConsent otevírá předvolby delegovaným handlerem na třídu
        // .wpconsent-open-preferences; cílová URL není potřeba.
        $link = [
            'url' => '#',
            'is_external' => '',
            'nofollow' => '',
        ];

        if ('link' === $setting_key) {
            return $link;
        }

        $settings['link'] = $link;
        return $settings;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if ('yes' === ($settings['plain_link'] ?? '')) {
            $text = trim((string) ($settings['text'] ?? ''));
            if ('' === $text) {
                return;
            }
            echo '<a href="#" class="wpconsent-open-preferences gln-cookies-preferences-link">' . esc_html($text) . '</a>';
            return;
        }

        parent::render();
    }

    protected function render_button(?\Elementor\Widget_Base $instance = null)
    {
        if (null === $instance) {
            $instance = $this;
        }

        $instance->add_render_attribute('button', 'class', 'wpconsent-open-preferences');
        parent::render_button($instance);
    }

    protected function content_template()
    {
        ?>
        <# if (''===settings.text && ''===settings.selected_icon.value) { return; } if ('yes'===settings.plain_link) { #>
            <a href="#" class="wpconsent-open-preferences gln-cookies-preferences-link">{{{ settings.text }}}</a>
            <# return; } const optimizedMarkup=elementorCommon.config.experimentalFeatures.e_optimized_markup;
                view.addRenderAttribute('wrapper', 'class' , 'elementor-button-wrapper' );
                view.addRenderAttribute('button', 'class' , 'elementor-button' ); view.addRenderAttribute('button', 'class'
                , 'wpconsent-open-preferences' ); if (settings.button_css_id) { view.addRenderAttribute('button', 'id' ,
                settings.button_css_id); } if (settings.size) { view.addRenderAttribute('button', 'class' , 'elementor-size-' +
                settings.size); } else { view.addRenderAttribute('button', 'class' , 'elementor-size-sm' ); } if
                (settings.hover_animation) { view.addRenderAttribute('button', 'class' , 'elementor-animation-' +
                settings.hover_animation); } view.addRenderAttribute('icon', 'class' , 'elementor-button-icon' );
                view.addRenderAttribute('text', 'class' , 'elementor-button-text' );
                view.addInlineEditingAttributes('text', 'none' ); const iconHTML=elementor.helpers.renderIcon(view,
                settings.selected_icon, { 'aria-hidden' : true }, 'i' , 'object' ); const
                migrated=elementor.helpers.isIconMigrated(settings, 'selected_icon' ); #>
                <# if (!optimizedMarkup) { #>
                    <div {{{ view.getRenderAttributeString('wrapper') }}}>
                        <# } #>
                            <a {{{ view.getRenderAttributeString('button') }}}>
                                <span class="elementor-button-content-wrapper">
                                    <# if (settings.icon || settings.selected_icon) { #>
                                        <span {{{ view.getRenderAttributeString('icon') }}}>
                                            <# if ((migrated || !settings.icon) && iconHTML.rendered) { #>
                                                {{{ iconHTML.value }}}
                                                <# } else { #>
                                                    <i class="{{ settings.icon }}" aria-hidden="true"></i>
                                                    <# } #>
                                        </span>
                                        <# } #>
                                            <# if (settings.text) { #>
                                                <span {{{ view.getRenderAttributeString('text') }}}>{{{ settings.text }}}</span>
                                                <# } #>
                                </span>
                            </a>
                            <# if (!optimizedMarkup) { #>
                    </div>
                    <# } #>
                        <?php
    }
}
