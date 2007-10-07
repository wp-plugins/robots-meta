<?php
/*
Plugin Name: Robots Meta
Plugin URI: http://www.joostdevalk.nl/wordpress/robots-meta/
Description: This plugin allows you to add all the appropriate robots meta tags to your pages and feeds and handle unused archives.
Author: Joost de Valk
Version: 1.5
Author URI: http://www.joostdevalk.nl/
*/

if ( ! class_exists( 'RobotsMeta_Admin' ) ) {

	class RobotsMeta_Admin {

		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_submenu_page('plugins.php','Robots Meta Configuration', 'Robots Meta', 1, basename(__FILE__),array('RobotsMeta_Admin','config_page'));
			}
		} // end add_config_page()

		function config_page() {
			if ( isset($_POST['submitrobots']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the robots.txt file.'));
				check_admin_referer('robots-meta-udpaterobotstxt');
				
				$robots_file = "../robots.txt";
				$robotsnew = stripslashes($_POST['robotsnew']);
				if (is_writeable($robots_file)) {
					$f = fopen($robots_file, 'w+');
					fwrite($f, $robotsnew);
					fclose($f);
				}
			}
			if ( isset($_POST['submithtaccess']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the .htaccess.'));
				check_admin_referer('robots-meta-udpatehtaccesstxt');

				$htaccess_file = "../.htaccess";
				$htaccessnew = stripslashes($_POST['htaccessnew']);
				if (is_writeable($htaccess_file)) {
					$f = fopen($htaccess_file, 'w+');
					fwrite($f, $htaccessnew);
					fclose($f);
				}

			}
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Robots Meta options.'));
				check_admin_referer('robots-meta-udpatesettings');
				
				if (isset($_POST['admin'])) {
					$options['admin'] = true;
				} else {
					$options['admin'] = false;
				}

				if (isset($_POST['allfeeds'])) {
					$options['commentfeeds'] = true;
					$options['allfeeds'] = true;
				} else {
					$options['allfeeds'] = false;
				}

				if (isset($_POST['commentfeeds'])) {
					$options['commentfeeds'] = true;
				} else {
					$options['commentfeeds'] = false;
				}

				if (isset($_POST['disableauthor'])) {
					$options['disableauthor'] = true;
				} else {
					$options['disableauthor'] = false;
				}

				if (isset($_POST['disabledate'])) {
					$options['disabledate'] = true;
				} else {
					$options['disabledate'] = false;
				}

				if (isset($_POST['disableexplanation'])) {
					$options['disableexplanation'] = true;
				} else {
					$options['disableexplanation'] = false;
				}

				if (isset($_POST['login'])) {
					$options['login'] = true;
				} else {
					$options['login'] = false;
				}

				if (isset($_POST['nofollowcatsingle'])) {
					$options['nofollowcatsingle'] = true;
				} else {
					$options['nofollowcatsingle'] = false;
				}

				if (isset($_POST['nofollowcatpage'])) {
					$options['nofollowcatpage'] = true;
				} else {
					$options['nofollowcatpage'] = false;
				}

				if (isset($_POST['noodp'])) {
					$options['noodp'] = true;
				} else {
					$options['noodp'] = false;
				}

				if (isset($_POST['noydir'])) {
					$options['noydir'] = true;
				} else {
					$options['noydir'] = false;
				}
				
				if (isset($_POST['pagedhome'])) {
					$options['pagedhome'] = true;
				} else {
					$options['pagedhome'] = false;
				}

				if (isset($_POST['search'])) {
					$options['search'] = true;
				} else {
					$options['search'] = false;
				}

				if (isset($_POST['trailingslash'])) {
					$options['trailingslash'] = true;
				} else {
					$options['trailingslash'] = false;
				}
				
				$opt = serialize($options);
				update_option('RobotsMeta', $opt);
			}
			
			$opt  = get_option('RobotsMeta');
			$options = unserialize($opt);
			if ($options['allfeeds']) {
				$options['comments'] = true;
			}

			$robots_file = "../robots.txt";
			if (!is_file($robots_file))
				$error = 1;
			
			if (!$error && filesize($robots_file) > 0) {
				$f = fopen($robots_file, 'r');
				$content = fread($f, filesize($robots_file));
				$content = htmlspecialchars($content);
			}

			$error = 0;
			$htaccess_file = "../.htaccess";
			if (!is_file($htaccess_file))
				$error = 1;
			
			if (!$error && filesize($htaccess_file) > 0) {
				$f = fopen($htaccess_file, 'r');
				$contentht = fread($f, filesize($htaccess_file));
				$contentht = htmlspecialchars($contentht);
			}
			
			?>
			<div class="wrap">
				<style type="text/css" media="screen">
					p {
						max-width: 600px;
					}
				</style>
				<h2>Robots Meta Configuration</h2>
				<fieldset>
					<form action="" method="post" id="robotsmeta-conf">
						<?php wp_nonce_field('robots-meta-udpatesettings'); ?>
						<h3>RSS Feeds</h3>
						<span style="float: right; margin-top: -30px;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></span>
<?php
	global $wp_version;
	if ($wp_version >= "2.3") {
?>							
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="commentfeeds" name="commentfeeds" <?php if ( $options['commentfeeds'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="commentfeeds"><code>noindex</code> the comment RSS feeds</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							This will prevent the search engines from indexing your comment feeds.
						</p>
	<?php } ?>
<?php } ?>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="allfeeds" name="allfeeds" <?php if ( $options['allfeeds'] == true ) echo ' checked="checked" '; ?>/> 
								</td>
								<td>
									<label for="allfeeds"><code>noindex</code> <strong>all</strong> RSS feeds</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							This will prevent the search engines from indexing <strong>all your</strong> feeds. Highly discouraged.
						</p>
	<?php } ?>
						<h3>Prevent search engines from indexing the following pages:</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="search" name="search" <?php if ( $options['search'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="search">This site's search result pages</label>
								</td>
							</tr>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="pagedhome" name="pagedhome" <?php if ( $options['pagedhome'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="pagedhome">Subpages of the homepage</label>
								</td>
							</tr>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="logininput" name="login" <?php if ( $options['login'] == true ) echo ' checked="checked" '; ?>/>
								</td>
								<td>
									<label for="logininput">The login and register pages</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" id="admin" name="admin" <?php if ( $options['admin'] == true ) echo ' checked="checked" '; ?>/>
								</td>
								<td>
									<label for="admin">All admin pages</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							The first option prevents the search engines from indexing your search result pages, by a <code>noindex,follow</code> robots tag to them. The <code>follow</code> part means that search engine crawlers <em>will</em> spider the pages listed in the search results.<br/>
							<br/>
							The last two options prevent the search engines from indexing your login, register and admin pages.
						</p>
		<?php } ?>
						<h3>DMOZ and Yahoo! Directory settings</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="noodp" name="noodp" <?php if ( $options['noodp'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="noodp">Add <code>noodp</code> meta tag</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" id="noydir" name="noydir" <?php if ( $options['noydir'] == true ) echo ' checked="checked" '; ?>/>
								</td>
								<td>
									<label for="noydir">Add <code>noydir</code> meta robots tag</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							The first options prevents all search engines from using the DMOZ description for this site in the search results.<br/>
							<br/>
							The second options prevents Yahoo! from using the Yahoo! directory description for this site in the search results.<br/>
						</p>
		<?php } ?>
<?php
	if ($wp_version >= "2.3") {
?>						<h3>Permalink settings</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="trailingslash" name="trailingslash" <?php if ( $options['trailingslash'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="trailingslash">Enforce a trailing slash on all category and tag URL's</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							If you choose a permalink for your posts with <code>.html</code>, or anything else but a / on the end, this will force WordPress to add a trailing slash to non-post pages nonetheless.
						</p>
		<?php } ?>
<?php
	}
?>						<h3>Archive settings</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="disableauthor" name="disableauthor" <?php if ( $options['disableauthor'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="disableauthor">Disable the author archives</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" id="disabledate" name="disabledate" <?php if ( $options['disabledate'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="disabledate">Disable the date-based archives</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							If you're running a one author blog, the author archive will always look exactly the same as your homepage. And even though you may not link to it, others might, to do you harm. Disabling them here will make sure any link to those archives will be 301 redirected to the blog homepage.
						</p>
						<p>
							For the date based archives, the same applies: they probably look a lot like your homepage, and could thus be seen as duplicate content.
						</p>
		<?php } ?>
						<h3>Internal nofollow settings</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="nofollowcatpage" name="nofollowcatpage" <?php if ( $options['nofollowcatpage'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="nofollowcatpage">Nofollow category listings on pages</label>
								</td>
							</tr>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="nofollowcatsingle" name="nofollowcatsingle" <?php if ( $options['nofollowcatsingle'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="nofollowcatsingle">Nofollow category listings on single posts</label>
								</td>
							</tr>
						</table>
		<?php if (!$options['disableexplanation']) { ?>
						<p>
							If you're showing a category listing on all your single posts and pages, you're "leaking" quite a bit of PageRank towards these pages, whereas you probably want your single posts to rank. To prevent that from happening, check the two boxes above, and you will nofollow all the links to your categories from single posts and/or pages.
						</p>
		<?php } ?>
						<h3>Plugin settings</h3>
						<table>
							<tr>
								<td style="width: 30px;">
									<input type="checkbox" id="disableexplanation" name="disableexplanation" <?php if ( $options['disableexplanation'] == true ) echo ' checked="checked" '; ?>/></td>
								<td>
									<label for="disableexplanation">Hide verbose explanations of settings</label>
								</td>
							</tr>
						</table>
						<span style="float: right; margin-top: -30px;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></span>
					</form>
				</fieldset>
				<br/><br/>
				<h2>Robots.txt</h2>
				<fieldset>
					<form action="" method="post" id="robotstxt">
						<?php wp_nonce_field('robots-meta-udpaterobotstxt'); ?>
						<?php
							if (! is_writeable($robots_file)) {
								echo "<p><em>If your robots.txt were writable, you could edit it from here.</em></p>";
								$disabled = 'disabled="disabled"';
							} else {
								echo "<p>Edit the content of your robots.txt:</p>";
								$disabled = "";
							}
						?>
						<textarea cols="60" <?php echo $disabled; ?> rows="15" name="robotsnew"><?php echo $content ?></textarea>
						<?php if ($disabled == "") { ?>
						<span style="float: right; margin-top: -30px;" class="submit"><input type="submit" name="submitrobots" value="Alter Robots.txt &raquo;" /></span>
						<?php } ?>
					</form>
				</fieldset>
				<br/><br/>
				<h2>.htaccess</h2>
				<fieldset>
					<form action="" method="post" id="htaccess">
						<?php wp_nonce_field('robots-meta-udpatehtaccesstxt'); ?>
						<?php
							if (! is_writeable($htaccess_file)) {
								echo "<p><em>If your .htaccess were writable, you could edit it from here.</em></p>";
								$disabled = 'disabled="disabled"';
							} else {
								echo "<p>Edit the content of your .htaccess:</p>";
								$disabled = "";
							}
						?>
						<textarea cols="60" <?php echo $disabled; ?> rows="15" name="htaccessnew"><?php echo $contentht ?></textarea>
						<?php if ($disabled == "") { ?>
						<span style="float: right; margin-top: -30px;" class="submit"><input type="submit" name="submithtaccess" value="Alter .htaccess &raquo;" /></span>
						<?php } ?>
					</form>
				</fieldset>
			</div>
			<?php
		}	// end add_config_page()
	} // end class RobotsMeta
}

function noindex_feed() {
	echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />'."\n";
}

function noindex_page() {
	echo '<meta name="robots" content="noindex" />'."\n";
}

function meta_robots() {
	global $options;
	
	$meta = "";

	if ( ($options['search'] && is_search()) || ($options['pagedhome'] && is_home() && get_query_var('paged') > 1) ) {
		$meta .= "noindex,follow";
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
	if ($meta != "") {
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

function archive_redirect() {
	global $options;
	
	if ($options['disabledate'] && is_date()) {
			wp_redirect(get_bloginfo('url'),301);
	}
	if ($options['disableauthor'] && is_author()) {
			wp_redirect(get_bloginfo('url'),301);
	}
}

function nofollow_category_listing($output) {
	global $options;
	
	if ( ($options['nofollowcatsingle'] && is_single() ) || ($options['nofollowcatpage'] && is_page() ) ) {
		$output = str_replace('href=','rel="nofollow" href=',$output);
		return $output;
	} else {
		return $output;
	}
}

$opt  = get_option('RobotsMeta');
$options = unserialize($opt);

global $wp_version;
if ($wp_version >= "2.3") {
	if ($options['allfeeds'] || $options['commentfeeds']) {
		add_action('commentrss2_head', 'noindex_feed');
	}
	if ($options['trailingslash']) {
		add_filter('user_trailingslashit', 'add_trailingslash', 10, 2);
	}
}
if ($options['allfeeds']) {
	add_action('rss_head', 'noindex_feed');
	add_action('rss2_head', 'noindex_feed');
}
if ($options['search'] || $options['noodp'] || $options ['noydir']) {
	add_action('wp_head', 'meta_robots');
}
if ($options['login']) {
	add_action('login_head', 'noindex_page');
}
if ($options['admin']) {
	add_action('admin_head', 'noindex_page');
}
if ($options['disabledate'] || $options['disableauthor']) {
	add_action('get_header', 'archive_redirect');
}
if ($options['nofollowcatsingle'] || $options['nofollowcatpage']) {
	add_filter('wp_list_categories','nofollow_category_listing');
}
add_action('admin_menu', array('RobotsMeta_Admin','add_config_page'));

?>
