<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if ( ! current_user_can( 'activate_plugins' ) )
       return;
 
$option_name = 'slug-length-extender-wpver';
delete_option($option_name);
