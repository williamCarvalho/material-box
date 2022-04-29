<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('materialbox_class');
delete_option('materialbox_selector');
delete_option('materialbox_in_duration');
delete_option('materialbox_out_duration');