<?php 

/*

Plugin Name: Longer Permalinks

Plugin URI: https://github.com/gecon/longer-permalinks/archive/master.zip

Description: This plugin allows longer permalinks by extending slug length (post_name) from default 200 to 3000. 
In a way that is future WordPress core updates compatible, by extending always the current/installed core functionality.
Useful for permalinks using non latin characters in URLs. Long permalinks will now work.

Author: Giannis Economou

Version: 1.17

Author URI: http://www.antithesis.gr

*/

defined( 'ABSPATH' ) OR exit;

define('LONGER_PERMALINKS_PLUGIN_VERSION', "117");
define('REDEF_FILE', WP_PLUGIN_DIR."/longer-permalinks/sanitize_override.inc");

register_activation_hook( __FILE__, 'longer_permalinks_plugin_install' );

$last_plugin_ver = get_site_option('longer-permalinks-pluginver');
$last_wp_ver = get_site_option('longer-permalinks-wpver');
$current_wp_ver = get_bloginfo('version');
$last_db_ver = get_site_option('longer-permalinks-dbver');
$current_db_ver = get_site_option('db_version');
$redefined = file_exists(REDEF_FILE);

// First install or updating plugin from 1.14-
if ( empty($last_plugin_ver) || ($last_plugin_ver == '') ) {
	//backup all post-names so far
	longer_permalinks_backup_existing_postnames();
	update_site_option( 'longer-permalinks-wpver', $current_wp_ver );
	update_site_option( 'longer-permalinks-dbver', $current_db_ver );
}
// Plugin update
if ($last_plugin_ver != LONGER_PERMALINKS_PLUGIN_VERSION) { //nothing special yet...
	update_option( 'longer-permalinks-pluginver', LONGER_PERMALINKS_PLUGIN_VERSION );
}


if ( ($last_wp_ver != $current_wp_ver) || ($last_db_ver != $current_db_ver) ) {
	longer_permalinks_alter_post_name_length();	

    if ($last_wp_ver != $current_wp_ver)
	        update_site_option( 'longer-permalinks-wpver', $current_wp_ver );
    if ($last_db_ver != $current_db_ver)
	        update_site_option( 'longer-permalinks-dbver', $current_db_ver );
}


if ( !$redefined || ($last_wp_ver != $current_wp_ver) ) {
	$redefined = redefine_sanitize_title_with_dashes();
}
if ($redefined) {
	include(REDEF_FILE);
	// replace the standard filter
	remove_filter( 'sanitize_title', 'sanitize_title_with_dashes' );
	add_filter( 'sanitize_title', 'longer_permalinks_sanitize_title_with_dashes', 10, 3 );
}


// restore longer permalinks in case of wp upgrade
// applying default wp db schema on upgrade will truncate post_name
//  we cannot filter anything on upgrade process, so we revert our longer slugs
if ( get_site_option('longer-permalinks-revert-needed') == 1 ) {
		longer_permalinks_revert_longer_titles();
		update_option( 'longer-permalinks-revert-needed', 0 );
}


//keep our longer slugs backups on post updates
add_action('save_post', 'longer_permalinks_backup_post_name_on_update', 10,2);
add_action('wp_insert_post', 'longer_permalinks_backup_post_name_on_update', 10,2);
//add_action('post_updated', 'longer_permalinks_backup_post_name_on_update', 10, 3); 
//add_filter('wp_unique_post_slug','longer_permalinks_backup_the_slug',10,6);

/*function longer_permalinks_backup_the_slug($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
	update_post_meta($post_ID, 'longer-permalinks-post-name-longer', $slug);
	return $slug;
}*/

function longer_permalinks_backup_post_name_on_update($post_ID, $post_after) {
        update_post_meta($post_ID, 'longer-permalinks-post-name-longer', $post_after->post_name);
}


function longer_permalinks_revert_longer_titles() {
        global $wpdb;

	//using direct sql for speed, avoid long delays on sites with a lot of posts
	$sql = "UPDATE {$wpdb->prefix}posts p JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID SET p.post_name = m.meta_value WHERE m.meta_key = 'longer-permalinks-post-name-longer';";
	$wpdb->query($sql);

}

function longer_permalinks_backup_existing_postnames() {
        global $wpdb;

	//using direct sql for speed, avoid delays on sites with a lot of posts
        $sql="INSERT INTO {$wpdb->prefix}postmeta (post_id, meta_key, meta_value) SELECT ID, 'longer-permalinks-post-name-longer', {$wpdb->prefix}posts.post_name FROM {$wpdb->prefix}posts";
        $wpdb->query($sql);
	
}

function redefine_sanitize_title_with_dashes() {
	if ( !is_writable( dirname(REDEF_FILE) ) ) {
		add_action('admin_notices','longer_permalinks_notice__error_dir_write_access');
		return 0;
	}
	if ( file_exists(REDEF_FILE) && !is_writable( REDEF_FILE ) ) {
	        add_action('admin_notices','longer_permalinks_notice__error_file_write_access');
		return 0;
	}

	$func = new ReflectionFunction('sanitize_title_with_dashes');
	$filename = $func->getFileName();
	$start_line = $func->getStartLine() - 1; 
	$end_line = $func->getEndLine();
	$length = $end_line - $start_line;

	$source = file($filename);
	$body = implode("", array_slice($source, $start_line, $length));

	$body = preg_replace('/function sanitize_title_with_dashes/','function longer_permalinks_sanitize_title_with_dashes',$body);
	$body = preg_replace('/\$title = utf8_uri_encode\( ?\$title\, 200\ ?\);/','$title = utf8_uri_encode($title, 3000);',$body, -1, $success);
	
	if ($success) {
		if (strlen($body) > 0) {
			$body = '<' . "?php\n" .$body;
			file_put_contents(REDEF_FILE, $body);
			return 1;
		}
		//indeed unexpected
		add_action('admin_notices','longer_permalinks_notice__error_unexpected');
	}
	else { 
		//could not apply core changes - new WordPress version probably (keypoint differences on sanitize_title_with_dashes)
		add_action('admin_notices','longer_permalinks_notice__error_extending_core');
	}
	return 0;
}

function longer_permalinks_notice__error_dir_write_access() {
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo _e('Could not write into plugin directory.') . REDEF_FILE ."<br>";
    echo _e("Plugin Longer Permalinks will not work. Please make plugin directory writable.");
    echo '</p></div>';
}

function longer_permalinks_notice__error_file_write_access() {
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo _e('Could not write file ') . REDEF_FILE ."<br>";
    echo _e("Plugin Longer Permalinks will not work. Please make file writable.");
    echo '</p></div>';
}

function longer_permalinks_notice__error_extending_core() {
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo _e('Could not apply required functionality to core'). "<br>";
    echo _e("Plugin Longer Permalinks could not extend required core functionality. The plugin seems not compatible with your WordPress version. Please contact developer about it.");
    echo '</p></div>';
}

function longer_permalinks_notice__error_unexpected() {
    echo '<div class="notice notice-error is-dismissible"><p>';
    echo _e('Could not apply required functionality to core'). "<br>";
    echo _e("Plugin Longer Permalinks could not extend required core functionality, due to an unexpected error. Please contact developer about it.");
    echo '</p></div>';
}

function longer_permalinks_plugin_install() {
	global $wpdb;

	if ( !current_user_can( 'activate_plugins' ) ) 
        	return;

	longer_permalinks_alter_post_name_length();

}

//update posts table field length
function longer_permalinks_alter_post_name_length() {
	global $wpdb;
	
        $sql = "CREATE TABLE {$wpdb->prefix}posts (
          post_name varchar(3000) DEFAULT '' NOT NULL
        );";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

        if($wpdb->last_error !== '') {
                trigger_error( _e('Plugin requires at least MySQL 5.0.3 - Plugin will fail'), E_USER_ERROR );
        }

    update_option( 'longer-permalinks-revert-needed', 1 ); // to update on next call
}


