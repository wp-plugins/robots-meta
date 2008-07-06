<?php
/*
Plugin Name: Robots Meta
Plugin URI: http://yoast.com/wordpress/robots-meta/
Description: This plugin allows you to add all the appropriate robots meta tags to your pages and feeds, disable unused archives and nofollow unnecessary links.
Author: Joost de Valk
Version: 3.0.3
Author URI: http://yoast.com/
*/

if ( ! class_exists( 'RobotsMeta_Admin' ) ) {

	class RobotsMeta_Admin {

		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_submenu_page('plugins.php','Robots Meta Configuration', 'Robots Meta', 1, basename(__FILE__),array('RobotsMeta_Admin','config_page'));
			}
		} // end add_config_page()

		function meta_box() {
			if ( function_exists('add_meta_box') ) {
				add_meta_box('robotsmeta','Robots Meta',array('RobotsMeta_Admin','noindex_option_fill'),'post');
				add_meta_box('robotsmeta','Robots Meta',array('RobotsMeta_Admin','noindex_option_fill'),'page');
			} else {
				add_action('dbx_post_sidebar', array('RobotsMeta_Admin','noindex_option'));
				add_action('dbx_page_sidebar', array('RobotsMeta_Admin','noindex_option'));				
			}
		}
		function robotsmeta_insert_post($pID) {
			global $wpdb;
			extract($_POST);
			$wpdb->query("UPDATE $wpdb->posts SET robotsmeta = '$robotsmeta' WHERE ID = $pID");
		}

		function noindex_option_fill() {
			?>
			<label for="meta_robots_index_follow" class="selectit"><input id="meta_robots_index_follow" name="robotsmeta" type="radio" value="index,follow" <?php if ($robotsmeta == "index,follow") echo 'checked="checked"'?>/> index, follow</label><br/>
			<label for="meta_robots_index_nofollow" class="selectit"><input id="meta_robots_index_nofollow" name="robotsmeta" type="radio" value="index,nofollow" <?php if ($robotsmeta == "index,nofollow") echo 'checked="checked"'?>/> index, nofollow</label><br/>
			<label for="meta_robots_noindex_follow" class="selectit"><input id="meta_robots_noindex_follow" name="robotsmeta" type="radio" value="noindex,follow" <?php if ($robotsmeta == "noindex,follow") echo 'checked="checked"'?>/> noindex, follow</label><br/>
			<label for="meta_robots_noindex_nofollow" class="selectit"><input id="meta_robots_noindex_nofollow" name="robotsmeta" type="radio" value="noindex,nofollow" <?php if ($robotsmeta == "noindex,nofollow") echo 'checked="checked"'?>/> noindex, nofollow</label><br/>
			<?php
		}

		function noindex_option() {
			global $post;
			$robotsmeta = $post->robotsmeta;
			if (!isset($robotsmeta) || $robotsmeta == "") {
				$robotsmeta = "index,follow";
			}
			if ( current_user_can('edit_posts') ) { ?>
			<fieldset id="robotsmeta-noindexoption" class="dbx-box">
			<h3 class="dbx-handle">Robots Meta</h3>
			<div class="dbx-content">
				<label for="meta_robots_index_follow" class="selectit"><input id="meta_robots_index_follow" name="robotsmeta" type="radio" value="index,follow" <?php if ($robotsmeta == "index,follow") echo 'checked="checked"'?>/> index, follow</label>
				<label for="meta_robots_index_nofollow" class="selectit"><input id="meta_robots_index_nofollow" name="robotsmeta" type="radio" value="index,nofollow" <?php if ($robotsmeta == "index,nofollow") echo 'checked="checked"'?>/> index, nofollow</label>
				<label for="meta_robots_noindex_follow" class="selectit"><input id="meta_robots_noindex_follow" name="robotsmeta" type="radio" value="noindex,follow" <?php if ($robotsmeta == "noindex,follow") echo 'checked="checked"'?>/> noindex, follow</label>
				<label for="meta_robots_noindex_nofollow" class="selectit"><input id="meta_robots_noindex_nofollow" name="robotsmeta" type="radio" value="noindex,nofollow" <?php if ($robotsmeta == "noindex,nofollow") echo 'checked="checked"'?>/> noindex, nofollow</label>
			</div>
			</fieldset>
			<?php 
			}
		}
		
		function config_page() {
			if ( isset($_POST['submitrobots']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the robots.txt file.'));
				check_admin_referer('robots-meta-udpaterobotstxt');
				
				if (file_exists("../robots.txt")) {
					$robots_file = "../robots.txt";
				} else if (file_exists("../../robots.txt")) {
					$robots_file = "../../robots.txt";
				} else {
					$robots_file = false;
				}
				
				$robotsnew = stripslashes($_POST['robotsnew']);
				if ($robots_file != false && is_writeable($robots_file)) {
					$f = fopen($robots_file, 'w+');
					fwrite($f, $robotsnew);
					fclose($f);
				}
			}
			if ( isset($_POST['submithtaccess']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the .htaccess.'));
				check_admin_referer('robots-meta-udpatehtaccesstxt');

				if (file_exists("../.htaccess")) {
					$htaccess_file = "../.htaccess";
				} else if (file_exists("../../.htaccess")) {
					$htaccess_file = "../../.htaccess";
				} else {
					$htaccess_file = false;
				}

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

				if (isset($_POST['noindexauthor'])) {
					$options['noindexauthor'] = true;
				} else {
					$options['noindexauthor'] = false;
				}

				if (isset($_POST['noindexcat'])) {
					$options['noindexcat'] = true;
				} else {
					$options['noindexcat'] = false;
				}

				if (isset($_POST['noindexdate'])) {
					$options['noindexdate'] = true;
				} else {
					$options['noindexdate'] = false;
				}
				
				if (isset($_POST['noindextag'])) {
					$options['noindextag'] = true;
				} else {
					$options['noindextag'] = false;
				}

				if (isset($_POST['noarchive'])) {
					$options['noarchive'] = true;
				} else {
					$options['noarchive'] = false;
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

				if (isset($_POST['nofollowindexlinks'])) {
					$options['nofollowindexlinks'] = true;
				} else {
					$options['nofollowindexlinks'] = false;
				}

				if (isset($_POST['nofollowmeta'])) {
					$options['nofollowmeta'] = true;
				} else {
					$options['nofollowmeta'] = false;
				}

				if (isset($_POST['nofollowcommentlinks'])) {
					$options['nofollowcommentlinks'] = true;
				} else {
					$options['nofollowcommentlinks'] = false;
				}

				if (isset($_POST['nofollowtaglinks'])) {
					$options['nofollowtaglinks'] = true;
				} else {
					$options['nofollowtaglinks'] = false;
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
				
				if (isset($_POST['replacemetawidget'])) {
					$options['replacemetawidget'] = true;
				} else {
					$options['replacemetawidget'] = false;
				}

				if (isset($_POST['redirectsearch'])) {
					$options['redirectsearch'] = true;
				} else {
					$options['redirectsearch'] = false;
				}

				if (isset($_POST['trailingslash'])) {
					$options['trailingslash'] = true;
				} else {
					$options['trailingslash'] = false;
				}
				
				if (isset($_POST['googleverify'])) {
					$options['googleverify'] = $_POST['googleverify'];
				}

				if (isset($_POST['msverify'])) {
					$options['msverify'] = $_POST['msverify'];
				}

				if (isset($_POST['yahooverify'])) {
					$options['yahooverify'] = $_POST['yahooverify'];
				}

				if (isset($_POST['version'])) {
					$options['version'] = $_POST['version'];
				}
				$opt = serialize($options);
				update_option('RobotsMeta', $opt);
			}
			
			$opt  = get_option('RobotsMeta');
			$options = unserialize($opt);
			if ($options['allfeeds']) {
				$options['comments'] = true;
			}

			if (file_exists("../robots.txt")) {
				$robots_file = "../robots.txt";
			} else if (file_exists("../../robots.txt")) {
				$robots_file = "../../robots.txt";
			} else {
				$robots_file = false;
				$error = 1;
			}
			
			if (!$error && filesize($robots_file) > 0) {
				$f = fopen($robots_file, 'r');
				$content = fread($f, filesize($robots_file));
				$content = htmlspecialchars($content);
			}

			$error = 0;
			if (file_exists("../.htaccess")) {
				$htaccess_file = "../.htaccess";
			} else if (file_exists("../../.htaccess")) {
				$htaccess_file = "../../.htaccess";
			} else {
				$htaccess_file = false;
				$error = 1;
			}

			if (!$error && filesize($htaccess_file) > 0) {
				$f = fopen($htaccess_file, 'r');
				$contentht = fread($f, filesize($htaccess_file));
				$contentht = htmlspecialchars($contentht);
			}
			
			?>
			<div class="wrap">
				<h2>Robots Meta Configuration</h2>
				<form action="" method="post" id="robotsmeta-conf">
					<span style="border: 0; float: right; margin-top: -35px;" class="submit"><input type="submit" name="submit" value="Save Settings" /></span>
					<table class="form-table">
						<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('robots-meta-udpatesettings'); } ?>
						<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
						<tr>
							<th scope="row" valign="top">Plugin settings</th>
							<td>
								<input type="checkbox" id="disableexplanation" name="disableexplanation" <?php if ( $options['disableexplanation'] == true ) echo ' checked="checked" '; ?>/>
								<label for="disableexplanation">Hide verbose explanations of settings</label><br/>
							</td>
						</tr>						
						<tr valign="top">
							<th scope="row" width="20%">RSS Feeds</th>
							<td>
								<input type="checkbox" id="commentfeeds" name="commentfeeds" <?php if ( $options['commentfeeds'] == true ) echo ' checked="checked" '; ?>/>
								<label for="commentfeeds"><code>noindex</code> the comment RSS feeds</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									This will prevent the search engines from indexing your comment feeds.
								</p>
								<?php } ?>
								<input type="checkbox" id="allfeeds" name="allfeeds" <?php if ( $options['allfeeds'] == true ) echo ' checked="checked" '; ?>/> 
								<label for="allfeeds"><code>noindex</code> <strong>all</strong> RSS feeds</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									This will prevent the search engines from indexing <strong>all your</strong> feeds. Highly discouraged.
								</p>
								<?php } ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Prevent indexing of</th>
							<td>
								<input type="checkbox" id="search" name="search" <?php if ( $options['search'] == true ) echo ' checked="checked" '; ?>/>
								<label for="search">This site's search result pages</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Prevents the search engines from indexing your search result pages, by a <code>noindex,follow</code> robots tag to them. The <code>follow</code> part means that search engine crawlers <em>will</em> spider the pages listed in the search results.
								</p>
								<?php } ?>								
								<input type="checkbox" id="logininput" name="login" <?php if ( $options['login'] == true ) echo ' checked="checked" '; ?>/>
								<label for="logininput">The login and register pages</label><br/>
								<input type="checkbox" id="admin" name="admin" <?php if ( $options['admin'] == true ) echo ' checked="checked" '; ?>/>
								<label for="admin">All admin pages</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									The above two options prevent the search engines from indexing your login, register and admin pages.
								</p>
								<?php } ?>
								<input type="checkbox" id="pagedhome" name="pagedhome" <?php if ( $options['pagedhome'] == true ) echo ' checked="checked" '; ?>/>
								<label for="pagedhome">Subpages of the homepage</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Prevent the search engines from indexing your subpages, if you want them to only index your category and / or tag archives.
								</p>
								<?php } ?>
								<input type="checkbox" id="noindexauthor" name="noindexauthor" <?php if ( $options['noindexauthor'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noindexauthor">Author archives</label><br/>								
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									By default, WordPress creates author archives for each user, usually available under <code>/author/username</code>. If you have sufficient other archives, or yours is a one person blog, there's no need and you can best disable them or prevent search engines from indexing them.
								</p>
								<?php } ?>
								<input type="checkbox" id="noindexdate" name="noindexdate" <?php if ( $options['noindexdate'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noindexdate">Date-based archives</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you want to offer your users the option of crawling your site by date, but have ample other ways for the search engines to find the content on your site, I highly encourage you to prevent your date-based archives from being indexed.
								</p>
								<?php } ?>
								<input type="checkbox" id="noindexcat" name="noindexcat" <?php if ( $options['noindexcat'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noindexcat">Category archives</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you're using tags as your only way of structure on your site, you would probably be better off when you prevent your categories from being indexed.
								</p>
								<?php } ?>
								<input type="checkbox" id="noindextag" name="noindextag" <?php if ( $options['noindextag'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noindextag">Tag archives</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Read the categories explanation above for categories and switch the words category and tag around ;)
								</p>
								<?php } ?>
								<input type="checkbox" id="noarchive" name="noarchive" <?php if ( $options['noarchive'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noarchive">Add <code>noarchive</code> meta tag</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Prevents archive.org and google to put copies of your pages into their archive/cache.to put copies of your pages into their archive/cache.
								</p>
								<?php } ?>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">DMOZ<br/> and Yahoo! Directory</th>
							<td>
								<input type="checkbox" id="noodp" name="noodp" <?php if ( $options['noodp'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noodp">Add <code>noodp</code> meta tag</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Prevents all search engines from using the DMOZ description for this site in the search results.
								</p>
								<?php } ?>
								<input type="checkbox" id="noydir" name="noydir" <?php if ( $options['noydir'] == true ) echo ' checked="checked" '; ?>/>
								<label for="noydir">Add <code>noydir</code> meta robots tag</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Prevents Yahoo! from using the Yahoo! directory description for this site in the search results.
								</p>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top">Permalink settings</th>
							<td>
								<input type="checkbox" id="trailingslash" name="trailingslash" <?php if ( $options['trailingslash'] == true ) echo ' checked="checked" '; ?>/>
								<label for="trailingslash">Enforce a trailing slash on all category and tag URL's</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you choose a permalink for your posts with <code>.html</code>, or anything else but a / on the end, this will force WordPress to add a trailing slash to non-post pages nonetheless.
								</p>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top">Archive settings</th>
							<td>
								<input type="checkbox" id="disableauthor" name="disableauthor" <?php if ( $options['disableauthor'] == true ) echo ' checked="checked" '; ?>/>
								<label for="disableauthor">Disable the author archives</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you're running a one author blog, the author archive will always look exactly the same as your homepage. And even though you may not link to it, others might, to do you harm. Disabling them here will make sure any link to those archives will be 301 redirected to the blog homepage.
								</p>
								<?php } ?>
								<input type="checkbox" id="disabledate" name="disabledate" <?php if ( $options['disabledate'] == true ) echo ' checked="checked" '; ?>/>
								<label for="disabledate">Disable the date-based archives</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									For the date based archives, the same applies: they probably look a lot like your homepage, and could thus be seen as duplicate content.
								</p>
								<?php } ?>
								<input type="checkbox" id="redirectsearch" name="redirectsearch" <?php if ( $options['redirectsearch'] == true ) echo ' checked="checked" '; ?>/>
								<label for="redirectsearch">Redirect search results pages when referrer is external</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Redirect people coming to a search page on your site from elsewhere to your homepage, prevents people from linking to search results on your site.
								</p>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top">Internal nofollow settings</th>
							<td>
								<input type="checkbox" id="nofollowcatpage" name="nofollowcatpage" <?php if ( $options['nofollowcatpage'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowcatpage">Nofollow category listings on pages</label><br/>
								<input type="checkbox" id="nofollowcatsingle" name="nofollowcatsingle" <?php if ( $options['nofollowcatsingle'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowcatsingle">Nofollow category listings on single posts</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you're showing a category listing on all your single posts and pages, you're "leaking" quite a bit of PageRank towards these pages, whereas you probably want your single posts to rank. To prevent that from happening, check the two boxes above, and you will nofollow all the links to your categories from single posts and/or pages.
								</p>
								<?php } ?>
								<input type="checkbox" id="nofollowindexlinks" name="nofollowindexlinks" <?php if ( $options['nofollowindexlinks'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowindexlinks">Nofollow outbound links on the frontpage</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you want to keep the link-juice on your front page to yourself, enable this, and you will only pass link-juice from your post pages.
								</p>
								<?php } ?>
								<input type="checkbox" id="nofollowtaglinks" name="nofollowtaglinks" <?php if ( $options['nofollowtaglinks'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowtaglinks">Nofollow the links to your tag pages</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									If you've decided to keep your tag pages from being indexed, you might as well stop throwing link-juice at them on each post...
								</p>
								<?php } ?>
								<input type="checkbox" id="nofollowmeta" name="nofollowmeta" <?php if ( $options['nofollowmeta'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowmeta">Nofollow login and registration links</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									This might have happened to you: logging in to your admin panel to notice that is has become PR6... Nofollow those admin and login links, there's no use flowing PageRank to those pages!
								</p>
								<?php } ?>
								<input type="checkbox" id="nofollowcommentlinks" name="nofollowcommentlinks" <?php if ( $options['nofollowcommentlinks'] == true ) echo ' checked="checked" '; ?>/>
								<label for="nofollowcommentlinks">Nofollow comments links</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									Simple way to decrease the number of links on your pages: nofollow all the links pointing to comment sections.
								</p>
								<?php } ?>
								<input type="checkbox" id="replacemetawidget" name="replacemetawidget" <?php if ( $options['replacemetawidget'] == true ) echo ' checked="checked" '; ?>/>
								<label for="replacemetawidget">Replace the Meta Widget with a nofollowed one</label><br/>
								<?php if (!$options['disableexplanation']) { ?>
								<p>
									By default the Meta widget links to your RSS feeds and to WordPress.org with a follow link, this will replace that widget by a custom one in which all these links are nofollowed.
								</p>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top">Webmaster Tools</th>
							<td colspan="2">
								<label for="googleverify">Verify meta value for Google Webmaster Tools:</label><br/>
								<input size="50" type="text" id="googleverify" name="googleverify" <?php echo 'value="'.$options['googleverify'].'" '; ?>/><br/>
								<label for="yahooverify">Verify meta value for Yahoo! Site Explorer:</label><br/>
								<input size="50" type="text" id="yahooverify" name="yahooverify" <?php echo 'value="'.$options['yahooverify'].'" '; ?>/><br/>
								<label for="msverify">Verify meta value for Microsoft Webmaster Portal:</label><br/>
								<input size="50" type="text" id="msverify" name="msverify" <?php echo 'value="'.$options['msverify'].'" '; ?>/>
							</td>
						</tr>
					</table>
					<br/>
					<span class="submit" style="border: 0;"><input type="submit" name="submit" value="Save Settings" /></span>
				</form>
				<br/><br/>
<?php if ($robots_file != false) { ?>
				<h2>Robots.txt</h2>
				<form action="" method="post" id="robotstxt">
					<?php wp_nonce_field('robots-meta-udpaterobotstxt'); ?>
					<?php
						if (!is_writeable($robots_file)) {
							echo "<p><em>If your robots.txt were writable, you could edit it from here.</em></p>";
							$disabled = 'disabled="disabled"';
						} else {
							echo "<p>Edit the content of your robots.txt:</p>";
							$disabled = "";
							?>
							<textarea cols="60" <?php echo $disabled; ?> rows="15" name="robotsnew"><?php echo $content ?></textarea><br/>
							<br/>
							<span class="submit" style="border: 0;"><input type="submit" name="submitrobots" value="Save changes to Robots.txt" /></span>
					<?php } ?>
				</form>
				<br/><br/>
<?php
}
if ($htaccess_file != false) {
?>
				<h2>.htaccess</h2>
				<form action="" method="post" id="htaccess">
					<?php wp_nonce_field('robots-meta-udpatehtaccesstxt'); ?>
					<?php
						if (! is_writeable($htaccess_file)) {
							echo "<p><em>If your .htaccess were writable, you could edit it from here.</em></p>";
							$disabled = 'disabled="disabled"';
						} else {
							echo "<p>Edit the content of your .htaccess:</p>";
							$disabled = "";
							?>
							<textarea cols="60" <?php echo $disabled; ?> rows="15" name="htaccessnew"><?php echo $contentht ?></textarea><br/>
							<br/>
							<span class="submit" style="border:0;"><input type="submit" name="submithtaccess" value="Save changes to .htaccess" /></span>
					<?php } ?>
				</form>
<?php } ?>
			</div>
			<?php
	}
	} // end class RobotsMeta
}

function noindex_feed() {
	echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />'."\n";
}

function noindex_page() {
	echo '<meta name="robots" content="noindex" />'."\n";
}

function meta_robots() {
	$opt  = get_option('RobotsMeta');
	$options = unserialize($opt);
	
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
		echo '<!--Meta tags added by Robots Meta: http://yoast.com/wordpress/meta-robots-wordpress-plugin/ -->'."\n";
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
	
	$opt  = get_option('RobotsMeta');
	$options = unserialize($opt);
	
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
	$opt  = get_option('RobotsMeta');
	$options = unserialize($opt);
	
	if ( ($options['nofollowcatsingle'] && (is_single() || is_search()) ) || ($options['nofollowcatpage'] && is_page() || is_category() || is_tag() ) ) {
		$output = nofollow_link($output);
		return $output;
	} else {
		return $output;
	}
}

function google_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) ) {
		$opt  = get_option('RobotsMeta');
		$options = unserialize($opt);
		echo '<meta name="verify-v1" content="'.$options['googleverify'].'" />'."\n";
	}
}

function yahoo_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) ) {
		$opt  = get_option('RobotsMeta');
		$options = unserialize($opt);
		echo '<meta name="y_key" content="'.$options['yahooverify'].'" />'."\n";
	}
}

function ms_verify() {
	if (is_home() || (function_exists('is_frontpage') && is_frontpage()) ) {
		$opt  = get_option('RobotsMeta');
		$options = unserialize($opt);
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
	$opt  = get_option('RobotsMeta');
	$options = unserialize($opt);
	if ($options['version'] < "2.3") {
		echo $wpdb->get_col_info('robotsmeta');
		$wpdb->query("ALTER TABLE $wpdb->posts ADD COLUMN robotsmeta varchar(64)");
		$options['version'] = "2.3";
	}
	if ($options['version'] < "25") {
		$options['version'] = "25";
	}
	$opt = serialize($options);
	update_option('RobotsMeta', $opt);
}
function echo_nofollow() {
	return ' rel="nofollow"';
}

$opt  = get_option('RobotsMeta');
$options = unserialize($opt);

global $wp_version;
if ($wp_version >= "2.3") {
	if ($options['allfeeds'] || $options['commentfeeds']) {
		add_action('commentsrss2_head', 'noindex_feed');
	}
	if ($options['trailingslash']) {
		add_filter('user_trailingslashit', 'add_trailingslash', 10, 2);
	}
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

add_action('admin_menu', array('RobotsMeta_Admin','add_config_page'));
add_action('admin_menu', array('RobotsMeta_Admin','meta_box'));

add_action('wp_insert_post', array('RobotsMeta_Admin','robotsmeta_insert_post'));
if ($options['version'] < '25') {
	robotsmeta_update();
}
?>
