<?php
/*
Plugin Name: Robots Meta
Plugin URI: http://yoast.com/wordpress/robots-meta/
Description: This plugin allows you to add all the appropriate robots meta tags to your pages and feeds, disable unused archives and nofollow unnecessary links.
Author: Joost de Valk
Version: 3.2
Author URI: http://yoast.com/
*/

if ( ! class_exists( 'RobotsMeta_Admin' ) ) {

	require_once('yst_plugin_tools.php');
	
	class RobotsMeta_Admin extends Yoast_Plugin_Admin {

		var $hook 		= 'robots-meta';
		var $filename	= 'robots-meta/robots-meta.php';
		var $longname	= 'Robots Meta Configuration';
		var $shortname	= 'Robots Meta';
		var $optionname = 'RobotsMeta';
		var $ozhicon	= 'tag.png';
		
		function meta_box() {
			add_meta_box('robotsmeta','Robots Meta',array('RobotsMeta_Admin','noindex_option_fill'),'post','side');
			add_meta_box('robotsmeta','Robots Meta',array('RobotsMeta_Admin','noindex_option_fill'),'page','side');
		}

		function RobotsMeta_Admin() {
			add_action( 'admin_menu', array(&$this, 'register_settings_page') );
			add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
			add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
			
			add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
			add_action('admin_print_scripts', array(&$this,'robots_meta_admin_script'));
			add_action('admin_print_styles', array(&$this,'config_page_styles'));	
			
			add_action('wp_dashboard_setup', array(&$this,'widget_setup'));	
			add_action('admin_menu', array('RobotsMeta_Admin','meta_box'));
			add_action('wp_insert_post', array('RobotsMeta_Admin','robotsmeta_insert_post'));
		}
		
		function robots_meta_admin_script() {
			wp_enqueue_script('robots-meta-script',WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/robots-meta-admin.js',array('jquery'));
		}
		
		function robotsmeta_insert_post($pID) {
			global $wpdb;
			extract($_POST);
			$wpdb->query("UPDATE $wpdb->posts SET robotsmeta = '$robotsmeta' WHERE ID = $pID");
		}

		function noindex_option_fill() {
			global $post;
			$robotsmeta = $post->robotsmeta;
			if (!isset($robotsmeta) || $robotsmeta == "") {
				$robotsmeta = "index,follow";
			}			
			?>
			<label for="meta_robots_index_follow" class="selectit"><input id="meta_robots_index_follow" name="robotsmeta" type="radio" value="index,follow" <?php if ($robotsmeta == "index,follow") echo 'checked="checked"'?>/> index, follow</label><br/>
			<label for="meta_robots_index_nofollow" class="selectit"><input id="meta_robots_index_nofollow" name="robotsmeta" type="radio" value="index,nofollow" <?php if ($robotsmeta == "index,nofollow") echo 'checked="checked"'?>/> index, nofollow</label><br/>
			<label for="meta_robots_noindex_follow" class="selectit"><input id="meta_robots_noindex_follow" name="robotsmeta" type="radio" value="noindex,follow" <?php if ($robotsmeta == "noindex,follow") echo 'checked="checked"'?>/> noindex, follow</label><br/>
			<label for="meta_robots_noindex_nofollow" class="selectit"><input id="meta_robots_noindex_nofollow" name="robotsmeta" type="radio" value="noindex,nofollow" <?php if ($robotsmeta == "noindex,nofollow") echo 'checked="checked"'?>/> noindex, nofollow</label><br/>
			<?php
		}
		
		function config_page() {
			if ( isset($_POST['submitrobots']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the robots.txt file.'));
				check_admin_referer('robots-meta-udpaterobotstxt');
				
				if (file_exists($_SERVER['DOCUMENT_ROOT']."/robots.txt")) {
					$robots_file = $_SERVER['DOCUMENT_ROOT']."/robots.txt";
					$robotsnew = stripslashes($_POST['robotsnew']);
					if (is_writable($robots_file)) {
						$f = fopen($robots_file, 'w+');
						fwrite($f, $robotsnew);
						fclose($f);
					}
				} 
			}
			
			if ( isset($_POST['submithtaccess']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the .htaccess file.'));
				check_admin_referer('robots-meta-udpatehtaccesstxt');

				if (file_exists($_SERVER['DOCUMENT_ROOT']."/.htaccess")) {
					$htaccess_file = $_SERVER['DOCUMENT_ROOT']."/.htaccess";
					$htaccessnew = stripslashes($_POST['htaccessnew']);
					if (is_writeable($htaccess_file)) {
						$f = fopen($htaccess_file, 'w+');
						fwrite($f, $htaccessnew);
						fclose($f);
					}
				} 
			}
			
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Robots Meta options.'));
				check_admin_referer('robots-meta-udpatesettings');
				
				foreach (array('admin', 'allfeeds', 'commentfeeds', 'disableauthor', 'disabledate', 
				          'disableexplanation', 'login', 'noindexauthor', 'noindexcat', 'noindexdate', 
					  'noindextag', 'noarchive', 'nofollowcatsingle', 'nofollowcatpage', 
					  'nofollowindexlinks', 'nofollowmeta', 'nofollowmeta', 'nofollowcommentlinks', 
					  'nofollowtaglinks', 'noodp', 'noydir', 'pagedhome', 'search', 'replacemetawidget',
					  'redirectsearch', 'trailingslash', 'redirectattachment') 
				as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}
			        foreach (array('googleverify', 'msverify', 'yahooverify', 'version') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = $_POST[$option_name];
					}
				}

				if ($options['allfeeds']) {
					$options['commentfeeds'] = true;
				} 
				
				update_option('RobotsMeta', $options);
			}
			
			$options  = get_option('RobotsMeta');
			
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://cdn.yoast.com/theme/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>Robots Meta Configuration</h2>
				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<form action="" method="post" id="robotsmeta-conf">
								<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('robots-meta-udpatesettings'); } ?>
								<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
								<?php 
									$this->postbox('pluginsettings','Plugin Settings',$this->checkbox('disableexplanation','Hide verbose explanations of settings')); 
									
									$content = $this->checkbox('commentfeeds','<code>noindex</code> the comment RSS feeds');
									$content .= '<p class="desc">This will prevent the search engines from indexing your comment feeds.</p>';
									
									$content .= $this->checkbox('allfeeds','<code>noindex</code> <strong>all</strong> RSS feeds');
									$content .= '<p class="desc">This will prevent the search engines from indexing <strong>all your</strong> feeds. Highly discouraged.</p>';

									$this->postbox('rssfeeds','RSS Feeds',$content); 


									$content = $this->checkbox('search','This site\'s search result pages');
									$content .= '<p class="desc">Prevents the search engines from indexing your search result pages, by a <code>noindex,follow</code> robots tag to them. The <code>follow</code> part means that search engine crawlers <em>will</em> spider the pages listed in the search results.</p>';
									$content .= $this->checkbox('logininput','The login and register pages');
									$content .= '<p class="desc">(warning: don\'t enable this if you have the <a href="http://wordpress.org/extend/plugins/minimeta-widget/">minimeta widget</a> installed!)</p>';
									$content .= $this->checkbox('admin','All admin pages');
									$content .= '<p class="desc">The above two options prevent the search engines from indexing your login, register and admin pages.</p>';
									$content .= $this->checkbox('pagedhome','Subpages of the homepage');
									$content .= '<p class="desc">Prevent the search engines from indexing your subpages, if you want them to only index your category and / or tag archives.</p>';
									$content .= $this->checkbox('noindexauthor','Author archives');
									$content .= '<p class="desc">By default, WordPress creates author archives for each user, usually available under <code>/author/username</code>. If you have sufficient other archives, or yours is a one person blog, there\'s no need and you can best disable them or prevent search engines from indexing them.</p>';
									
									$content .= $this->checkbox('noindexdate','Date-based archives');
									$content .= '<p class="desc">If you want to offer your users the option of crawling your site by date, but have ample other ways for the search engines to find the content on your site, I highly encourage you to prevent your date-based archives from being indexed.</p>';
									$content .= $this->checkbox('noindexcat','Category archives');
									$content .= '<p class="desc">If you\'re using tags as your only way of structure on your site, you would probably be better off when you prevent your categories from being indexed.</p>';

									$content .= $this->checkbox('noindextag','Tag archives');
									$content .= '<p class="desc">Read the categories explanation above for categories and switch the words category and tag around ;)</p>';
									$content .= $this->checkbox('noarchive','Add <code>noarchive</code> meta tag');
									$content .= '<p class="desc">Prevents archive.org and Google from putting copies of your pages into their archive/cache.to put copies of your pages into their archive/cache.</p>';
									
									$this->postbox('preventindexing','Prevent Indexing',$content); 
									
									$content = $this->checkbox('noodp','Add <code>noodp</code> meta robots tag');
									$content .= '<p class="desc">Prevents all search engines from using the DMOZ description for this site in the search results.</p>';
									$content .= $this->checkbox('noydir','Add <code>noydir</code> meta robots tag');
									$content .= '<p class="desc">Prevents Yahoo! from using the Yahoo! directory description for this site in the search results.</p>';
									
									$this->postbox('directories','DMOZ and Yahoo! Directory',$content); 
									
									$content = $this->checkbox('trailingslash','Enforce a trailing slash on all category and tag URL\'s');
									$content .= '<p class="desc">If you choose a permalink for your posts with <code>.html</code>, or anything else but a / on the end, this will force WordPress to add a trailing slash to non-post pages nonetheless.</p>';

									$content .= $this->checkbox('redirectattachment','Redirect attachment URL\'s to parent post URL.');
									$content .= '<p class="desc">Attachments to posts are stored in the database as posts, this means they\'re accessible under their own URL\'s if you do not redirect them, enabling this will redirect them to the post they were attached to.</p>';
									
									$this->postbox('permalinks','Permalink Settings',$content); 
									
									$content = $this->checkbox('disableauthor','Disable the author archives');
									$content .= '<p class="desc">If you\'re running a one author blog, the author archive will always look exactly the same as your homepage. And even though you may not link to it, others might, to do you harm. Disabling them here will make sure any link to those archives will be 301 redirected to the blog homepage.</p>';
									
									$content .= $this->checkbox('disabledate','Disable the date-based archives');
									$content .= '<p class="desc">For the date based archives, the same applies: they probably look a lot like your homepage, and could thus be seen as duplicate content.</p>';
									
									$content .= $this->checkbox('redirectsearch','Redirect search results pages when referrer is external');
									$content .= '<p class="desc">Redirect people coming to a search page on your site from elsewhere to your homepage, prevents people from linking to search results on your site.</p>';
									
									$this->postbox('archivesettings','Archive Settings', $content);
									
									$content = $this->checkbox('nofollowcatpage','Nofollow category listings on pages');
									$content .= $this->checkbox('nofollowcatsingle','Nofollow category listings on single posts');
									$content .= '<p class="desc">If you\'re showing a category listing on all your single posts and pages, you\'re "leaking" quite a bit of PageRank towards these pages, whereas you probably want your single posts to rank. To prevent that from happening, check the two boxes above, and you will nofollow all the links to your categories from single posts and/or pages.</p>';
									
									$content .= $this->checkbox('nofollowindexlinks','Nofollow outbound links on the frontpage');
									$content .= '<p class="desc">If you want to keep the link-juice on your front page to yourself, enable this, and you will only pass link-juice from your post pages.</p>';
									
									$content .= $this->checkbox('nofollowtaglinks','Nofollow the links to your tag pages');
									$content .= '<p class="desc">If you\'ve decided to keep your tag pages from being indexed, you might as well stop throwing link-juice at them on each post...</p>';
									
									$content .= $this->checkbox('nofollowmeta','Nofollow login and registration links');
									$content .= '<p class="desc">This might have happened to you: logging in to your admin panel to notice that it has become PR6... Nofollow those admin and login links, there\'s no use flowing PageRank to those pages!</p>';
									
									$content .= $this->checkbox('nofollowcommentlinks','Nofollow comments links');
									$content .= '<p class="desc">Simple way to decrease the number of links on your pages: nofollow all the links pointing to comment sections.</p>';
									
									$content .= $this->checkbox('replacemetawidget','Replace the Meta Widget with a nofollowed one');
									$content .= '<p class="desc">By default the Meta widget links to your RSS feeds and to WordPress.org with a follow link, this will replace that widget by a custom one in which all these links are nofollowed.</p>';
									
									$this->postbox('internalnofollow','Internal nofollow settings',$content);
									
									$content = $this->textinput('googleverify','Verify meta value for Google Webmaster Tools');
									$content .= $this->textinput('yahooverify','Verify meta value for Yahoo! Site Explorer');
									$content .= $this->textinput('msverify','Verify meta value for Microsoft Webmaster Portal');

									$this->postbox('webmastertools','Webmaster Tools',$content);
								?>
								<div class="submit"><input type="submit" class="button-primary" name="submit" value="Save Robots Meta Settings" /></div>
							</form>
							<?php 

							if (file_exists($_SERVER['DOCUMENT_ROOT'] ."/robots.txt")) {
								$robots_file = $_SERVER['DOCUMENT_ROOT'] ."/robots.txt";
								$f = fopen($robots_file, 'r');
								$content = fread($f, filesize($robots_file));
								$robotstxtcontent = htmlspecialchars($content);

								if (!is_writable($robots_file)) {
									$content = "<p><em>If your robots.txt were writable, you could edit it from here.</em></p>";
									$content .= '<textarea disabled="disabled" style="width: 90%;" rows="15" name="robotsnew">'.$robotstxtcontent.'</textarea><br/>';
								} else {
									$content = '<form action="" method="post">';
									$content .= wp_nonce_field('robots-meta-udpaterobotstxt','_wpnonce',true,false);
									$content .= "<p>Edit the content of your robots.txt:</p>";
									$content .= '<textarea style="width: 90%;" rows="15" name="robotsnew">'.$robotstxtcontent.'</textarea><br/>';
									$content .= '<div class="submit"><input class="button" type="submit" name="submitrobots" value="Save changes to Robots.txt" /></div>';
									$content .= '</form>';
								}
								$this->postbox('robotstxt','Robots.txt',$content);
							}
							
							if (file_exists($_SERVER['DOCUMENT_ROOT'] ."/.htaccess")) {
								$htaccess_file = $_SERVER['DOCUMENT_ROOT'] ."/.htaccess";
								$f = fopen($htaccess_file, 'r');
								$contentht = fread($f, filesize($htaccess_file));
								$contentht = htmlspecialchars($contentht);

								if (!is_writable($htaccess_file)) {
									$content = "<p><em>If your .htaccess were writable, you could edit it from here.</em></p>";
									$content .= '<textarea disabled="disabled" style="width: 90%;" rows="15" name="robotsnew">'.$contentht.'</textarea><br/>';
								} else {
									$content = '<form action="" method="post">';
									$content .= wp_nonce_field('robots-meta-udpatehtaccesstxt','_wpnonce',true,false);
									$content =  "<p>Edit the content of your .htaccess:</p>";
									$content .= '<textarea style="width: 90%;" rows="15" name="robotsnew">'.$contentht.'</textarea><br/>';
									$content .= '<div class="submit"><input class="button" type="submit" name="submithtaccess" value="Save changes to .htaccess" /></div>';
									$content .= '</form>';
								}
								$this->postbox('htaccess','.htaccess file',$content);
							}
							?>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:20%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php
								$this->plugin_like();
								$this->plugin_support();
								$this->postbox('wpseo','SEO &amp; WordPress','<p>If you haven\'t read it yet, my <a href="http://yoast.com/articles/wordpress-seo/">article on WordPress SEO</a> is probably a good place to start learning about how to optimize your WordPress.</p>');
								$this->news(); 
							?>
						</div>
						<br/><br/><br/>
					</div>
				</div>
			</div>
			<?php
	}
	} // end class RobotsMeta
	$rma = new RobotsMeta_Admin();
}

function noindex_feed() {
	echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />'."\n";
}

function noindex_page() {
	echo '<meta name="robots" content="noindex" />'."\n";
}

function meta_robots() {
	$options  = get_option('RobotsMeta');
	
	$meta = "";
	if (is_single() || is_page()) {
		global $post;
		if ($post->robotsmeta != "index,follow") {
			$meta = $post->robotsmeta;	
		}
	} else if ( (is_author() && $options['noindexauthor']) || (is_category() && $options['noindexcat']) || (is_date() && $options['noindexdate']) || (function_exists(is_tag) && is_tag() && $options['noindextag']) || (is_search() && $options['search']) ) {
		$meta .= "noindex,follow";
	} else if (is_home()) {
		if ($options['pagedhome'] && get_query_var('paged') > 1) {
			$meta .= "noindex,follow";
		}
	}
	if ($options['noodp']) {
		if ($meta != "") {
			$meta .= ",";
		}
		$meta .= "noodp";
	} 
	if ($options['noydir']) {
		if ($meta != "") {
			$meta .= ",";
		}
		$meta .= "noydir";
	}
	if ($options['noarchive']) {
		if ($meta != "") {
			$meta .= ",";
		}
		$meta .= "noarchive";
	}
	if ($meta != "" && $meta != "index,follow") {
		echo '<!--Meta tags added by Robots Meta: http://www.joostdevalk.nl/wordpress/meta-robots-wordpress-plugin/ -->'."\n";
		echo '<meta name="robots" content="'.$meta.'" />'."\n";
	}
} 

function add_trailingslash($url, $type) {
	// trailing slashes for everything except is_single()
	// Thanks to Mark Jaquith for this
	if ( 'single' === $type ) {
		return $url;
	} else {
		return trailingslashit($url);
	}
}

function search_redirect() {
	if ($_GET['s'] &&  strpos($_SERVER['HTTP_REFERER'], get_bloginfo('url')) === false) {
		wp_redirect(get_bloginfo('url'),301);
		exit;
	}
}

function archive_redirect() {
	global $wp_query;
	
	$options  = get_option('RobotsMeta');
	
	if ($options['disabledate'] && $wp_query->is_date) {
		wp_redirect(get_bloginfo('url'),301);
		exit;
	}
	if ($options['disableauthor'] && $wp_query->is_author) {
		wp_redirect(get_bloginfo('url'),301);
		exit;
	}
}

function nofollow_link($output) {
	return str_replace('<a ','<a rel="nofollow" ',$output);
}

function nofollow_category_listing($output) {
	$options  = get_option('RobotsMeta');
	
	if ( ($options['nofollowcatsingle'] && (is_single() || is_search()) ) || ($options['nofollowcatpage'] && is_page() || is_category() || is_tag() ) ) {
		$output = nofollow_link($output);
		return $output;
	} else {
		return $output;
	}
}

function google_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) || (function_exists('is_front_page') && is_front_page()) ) {
		$options = get_option('RobotsMeta');
		echo '<meta name="verify-v1" content="'.$options['googleverify'].'" />'."\n";
	}
}

function yahoo_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) || (function_exists('is_front_page') && is_front_page()) ) {
		$options = get_option('RobotsMeta');
		echo '<meta name="y_key" content="'.$options['yahooverify'].'" />'."\n";
	}
}

function ms_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) || (function_exists('is_front_page') && is_front_page()) ) {
		$options = get_option('RobotsMeta');
		echo '<meta name="msvalidate.01" content="'.$options['msverify'].'" />'."\n";
	}
}

function add_nofollow($matches) {
	$origin = get_bloginfo('wpurl');
	if ((strpos($matches[2],$origin)) === false && ( strpos($matches[1],'rel="nofollow"') === false ) && ( strpos($matches[3],'rel="nofollow"') === false ) ) {
		$nofollow = ' rel="nofollow" ';
	} else {
		$nofollow = '';
	}
	return '<a href="' . $matches[2] . '"' . $nofollow . $matches[1] . $matches[3] . '>' . $matches[4] . '</a>';
}

function nofollow_index($output) {
	// Loop through the content of each post and add a nofollow when it's on the main page or a category page.
	if (is_home() || is_category()) {
		$anchorPattern = '/<a (.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
		$output = preg_replace_callback($anchorPattern,'add_nofollow',$output);
	}
	return $output;
}

function nofollow_taglinks($output) {
	$output = str_replace('rel="tag"','rel="nofollow tag"',$output);
	return $output;
}

function attachment_redirect() {
	global $post;
	if (is_attachment()) {
		wp_redirect(get_permalink($post->post_parent), 301);
	}
}

function widget_jdvmeta_init() {
	if (!function_exists('register_sidebar_widget'))
		return;

	function wp_jdvwidget_meta($args) {
		extract($args);
		$options = get_option('widget_meta');
		$title = empty($options['title']) ? __('Meta') : $options['title'];
	?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul>
				<?php wp_register(); ?>
				<li><?php wp_loginout(); ?></li>
				<li><a rel="nofollow" href="<?php bloginfo('rss2_url'); ?>" title="<?php echo attribute_escape(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
				<li><a rel="nofollow"href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php echo attribute_escape(__('The latest comments to all posts in RSS')); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
				<li><a rel="nofollow" href="http://wordpress.org/" title="<?php echo attribute_escape(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.')); ?>">WordPress.org</a></li>
				<?php wp_meta(); ?>
				</ul>
			<?php echo $after_widget; ?>
	<?php
	}

	register_sidebar_widget('meta','wp_jdvwidget_meta');
}

function robotsmeta_update() {
	global $wpdb;
	$options = get_option('RobotsMeta');
	if ($options['version'] < "2.3") {
		echo $wpdb->get_col_info('robotsmeta');
		$wpdb->query("ALTER TABLE $wpdb->posts ADD COLUMN robotsmeta varchar(64)");
		$options['version'] = "2.3";
	}
	if ($options['version'] < "25") {
		$options['version'] = "25";
	}
	update_option('RobotsMeta', $options);
}
function echo_nofollow() {
	return ' rel="nofollow"';
}

$options = get_option('RobotsMeta');
global $wp_version;

if ($options['allfeeds'] || $options['commentfeeds']) {
	add_action('commentsrss2_head', 'noindex_feed');
}
if ($options['trailingslash']) {
	add_filter('user_trailingslashit', 'add_trailingslash', 10, 2);
}
if ($options['redirectattachment']) {
	add_action('template_redirect', 'attachment_redirect',1);
}

if ($options['allfeeds']) {
	add_action('rss_head', 'noindex_feed');
	add_action('rss2_head', 'noindex_feed');
}

add_action('wp_head', 'meta_robots');

if ($options['login']) {
	add_action('login_head', 'noindex_page');
}
if ($options['admin']) {
	add_action('admin_head', 'noindex_page');
}
if ($options['disabledate'] || $options['disableauthor']) {
	add_action('wp', 'archive_redirect');
}
if ($options['redirectsearch']) {
	add_action('init', 'search_redirect');
}
if ($options['nofollowcatsingle'] || $options['nofollowcatpage']) {
	add_filter('wp_list_categories','nofollow_category_listing');
}
if ($options['nofollowmeta']) {
	add_filter('loginout','nofollow_link');
	add_filter('register','nofollow_link');
}
if ($options['nofollowcommentlinks']) {
	add_filter('comments_popup_link_attributes','echo_nofollow');
}
if ($options['nofollowtaglinks']) {
	add_filter('the_tags','nofollow_taglinks');
}
if ($options['googleverify']) {
	add_action('wp_head', 'google_verify');
}
if ($options['yahooverify']) {
	add_action('wp_head', 'yahoo_verify');
}
if ($options['msverify']) {
	add_action('wp_head', 'ms_verify');
}
if ($options['nofollowindexlinks']) {
	add_filter('the_content','nofollow_index');
}
if ($options['replacemetawidget']) {
	add_action('plugins_loaded', 'widget_jdvmeta_init');
}

?>