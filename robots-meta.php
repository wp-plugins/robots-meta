<?php
/*
Plugin Name: Robots Meta
Plugin URI: http://yoast.com/wordpress/robots-meta/
Description: This plugin is no longer maintained and replaced by Yoast SEO.
Author: Joost de Valk
Version: 3.4
Author URI: http://yoast.com/
*/

function robots_meta_upgrade_warning() {
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		echo '<div class="updated" id="message"><p>Robots Meta has been discontinued and isn\'t actively maintained. Please <a href="http://local.wordpress.dev/wp-admin/plugin-install.php?tab=search&s=yoast+seo">install Yoast SEO</a> as that\'s both well maintained and more secure, and disable this plugin.</p></div>';
	}
}

add_action( 'admin_notices', 'robots_meta_upgrade_warning' );
