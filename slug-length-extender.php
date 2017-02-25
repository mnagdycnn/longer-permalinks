<?php 

/*

Plugin Name: Slug length extender

Plugin URI: http://www.antithesis.gr/slug-length-extender.zip

Description: This plugin extends slug length (in characters) from default 200 to 3000. Long permalinks will now work.

Author: Giannis Economou

Version: 0.1

Author URI: http://www.antithesis.gr

*/

$redef_file = WP_PLUGIN_DIR."/slug-length-extender/sanitize_override.inc";


$last_wp_ver = get_option('slug-length-extender-wpver');
$current_wp_ver = get_bloginfo('version');

if ( !file_exists ($redef_file) || ($last_wp_ver != $current_wp_ver) ) {
		redefine_sanitize_title_with_dashes();
}

include($redef_file);

// remove standard filter
remove_filter( 'sanitize_title', 'sanitize_title_with_dashes' );
// add our custom filter
add_filter( 'sanitize_title', 'slug_length_extender_sanitize_title_with_dashes', 10, 3 );

//update wp version
update_option( 'slug-length-extender-wpver', $current_wp_ver );


function redefine_sanitize_title_with_dashes() {
	$func = new ReflectionFunction('sanitize_title_with_dashes');
	$filename = $func->getFileName();
	$start_line = $func->getStartLine() - 1; 
	$end_line = $func->getEndLine();
	$length = $end_line - $start_line;

	$source = file($filename);
	$body = implode("", array_slice($source, $start_line, $length));

	$body = preg_replace('/function sanitize_title_with_dashes/','function slug_length_extender_sanitize_title_with_dashes',$body);
	$body = preg_replace('/\$title = utf8_uri_encode\(\$title\, 200\);/','utf8_uri_encode($title, 3000);',$body);

	if (strlen($body) > 0) {
		$body = '<' . "?php\n" .$body;
		$outfile = WP_PLUGIN_DIR."/slug-length-extender/sanitize_override.inc"; 
		file_put_contents($outfile, $body);
		fclose ($outfile);
	}
}


function slug_length_extender_plugin_install() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	//Create custom table
	$sql="ALTER TABLE {$wpdb->prefix}posts modify post_name varchar(3000);";
	$wpdb->query($sql);
}
register_activation_hook( __FILE__, 'slug_length_extender_plugin_install' );

