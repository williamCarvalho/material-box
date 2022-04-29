<?php
/**
 * Plugin Name:       Material Box
 * Plugin URI:        
 * Description:       A simple and easy material design implementation of the Lightbox plugin using Materialize.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Will Carvalho
 * Author URI:        https://github.com/williamCarvalho
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Update URI:        
 * Text Domain:       material-box
 * Domain Path:       /languages
 * 
 * Material Box WordPress Plugin, Copyright (C) 2022, Will Carvalho
 * Material Box is distributed under the terms of the GNU GPL v3.0 or later.
 */

if (!class_exists('MaterialBoxMain')) {
    class MaterialBoxMain {
        protected $page_title;
        protected $menu_title;
        protected $capability;
        protected $slug;
        protected $callback;
        protected $position;

        function __construct() {
            $this->page_title = __('Material Box', 'material-box');
            $this->menu_title = __('Material Box', 'material-box');
            $this->capability = 'manage_options';
            $this->slug = 'material-box';
            $this->callback = array($this, 'materialbox_add_page_content');
            $this->position = 100;

            add_action('admin_menu', array($this, 'materialbox_add_page'));
            add_action('admin_init', array($this, 'materialbox_add_settings'));
            add_action('init', array($this, 'materialbox_init'));
        }

        function materialbox_enqueue() {
            wp_enqueue_style('materialize', plugins_url('/public/css/materialize.min.css', __FILE__), false, null);
            wp_enqueue_script('materialize', plugins_url('/pulic/js/materialize.min.js', __FILE__), array(), '', true );
        }

        function materialbox_init() {
            add_action("wp_enqueue_scripts", array($this, 'materialbox_enqueue'));
            add_action("wp_head", array($this, 'materialbox_script_head'));
            add_action("wp_footer", array($this, 'materialbox_script_footer'));

            load_plugin_textdomain('material-box', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        function materialbox_add_page() {
            add_theme_page($this->page_title, $this->menu_title, $this->capability, $this->slug, $this->callback);
        }

        function materialbox_add_page_content() {
            include 'includes/tpl/form.php';
        }

        function materialbox_add_settings() {
            // Activate section
            add_settings_section('materialbox_main_section',
                __('Activate', 'material-box'),
                array($this, 'materialbox_section_callback'),
                $this->slug);

            // Configuration section
            add_settings_section('materialbox_config_section',
                __('Configuration', 'material-box'),
                array($this, 'materialbox_section_callback'),
                $this->slug);

            $this->materialbox_setup_fields();
        }

        function materialbox_section_callback($arguments) {
            include "includes/tpl/$arguments[id].php";
        }

        function materialbox_setup_fields() {
            $fields = array(
                array(
                    'uid' => 'materialbox_class',
                    'label' => __('Selector class', 'material-box'),
                    'section' => 'materialbox_main_section',
                    'type' => 'text',
                    'options' => false,
                    'placeholder' => __('class name', 'material-box'),
                    'helper' => '',
                    'supplemental' => __('Put here the name of the class to which the effect will be applied.', 'material-box'),
                    'default' => ''
                ),
                array(
                    'uid' => 'materialbox_selector',
                    'label' => __('Query selector', 'material-box'),
                    'section' => 'materialbox_main_section',
                    'type' => 'select',
                    'options' => array(
                        'deactivated' => __("I don't want it now!", 'material-box'),
                        'class' => __('Active only for class', 'material-box'),
                        'images' => __('Active for all images', 'material-box'),
                    ),
                    'placeholder' => '',
                    'helper' => '',
                    'supplemental' => __('Select how the effect can be activated.', 'material-box'),
                    'default' => 'images'
                ),
                array(
                    'uid' => 'materialbox_in_duration',
                    'label' => __('In duration', 'material-box'),
                    'section' => 'materialbox_config_section',
                    'type' => 'number',
                    'options' => false,
                    'placeholder' => '',
                    'helper' => __('Default: 275', 'material-box'),
                    'supplemental' => __('Transition in duration in milliseconds.', 'material-box'),
                    'default' => '275'
                ),
                array(
                    'uid' => 'materialbox_out_duration',
                    'label' => __('Out duration', 'material-box'),
                    'section' => 'materialbox_config_section',
                    'type' => 'number',
                    'options' => false,
                    'placeholder' => '',
                    'helper' => __('Default: 200', 'material-box'),
                    'supplemental' => __('Transition out duration in milliseconds.', 'material-box'),
                    'default' => '200'
                ),
            );

            foreach ($fields as $field) {
                add_settings_field($field['uid'],
                    $field['label'],
                    array($this, 'materialbox_field_callback'),
                    $this->slug,
                    $field['section'],
                    $field);

                register_setting('materialbox_fields', $field['uid']);
            }
        }

        function materialbox_field_callback($arguments) {
            $value = get_option($arguments['uid']);

            if (!$value)
                $value = $arguments['default'];

            switch ($arguments['type']) {
                case 'number':
                case 'text':
                    include 'includes/tpl/input.php';
                    break;

                case 'select':
                    if (!empty($arguments['options']) && is_array($arguments['options']))
                        include 'includes/tpl/select.php';
                    break;              
            }

            if ($helper = $arguments['helper'])
                include 'includes/tpl/helper.php';

            if($supplimental = $arguments['supplemental'])
                include 'includes/tpl/supplemental.php';
        }

        function materialbox_script_head() {
            $value_selector = get_option('materialbox_selector');

            if ($value_selector && $value_selector != 'deactivated') { ?>
                <style type="text/css">
                    .material-placeholder {
                        cursor: pointer;
                    }

                    .material-placeholder img.active,
                    .wp-block-image .material-placeholder img.active,
                    .wp-block-gallery.has-nested-images figure.wp-block-image .material-placeholder img.active {
                        max-width: inherit !important;
                    }
                </style>
            <?php }
        }

        function materialbox_script_footer() {
            $value_class = get_option('materialbox_class');
            $value_selector = get_option('materialbox_selector');
            $in_duration = get_option('materialbox_in_duration');
            $out_duration = get_option('materialbox_out_duration');
            $query_selectors = "";

            if ($value_selector && $value_selector != 'deactivated') {
                $in_duration = $in_duration ? $in_duration : '275';
                $out_duration = $out_duration ? $out_duration : '200';

                if ('class' == $value_selector && $value_class)
                    $query_selectors = "img.$value_class, figure.$value_class img";
                else {
                    $post_types = get_post_types(array('public' => true), 'objects');

                    foreach ($post_types as $post_type)
                        $query_selectors.= ".type-$post_type->name img, ";

                    $query_selectors = trim($query_selectors, ", ");
                } ?>

                <script>
                    (function($) {
                        $(function() {
                            $("<?php echo $query_selectors; ?>").not("a img").materialbox({
                                inDuration: <?php echo $in_duration; ?>,
                                outDuration: <?php echo $out_duration; ?>
                            });
                        });
                    })(jQuery);
                </script>

            <?php }
        }
    }

    new MaterialBoxMain();
}