<?php
/*
Plugin Name: Wordpress admin bookmark folder maker
Plugin URI: http://tzafrir.net/wp-bookmark-maker/
Description: Creates a firefox importable bookmark folder with links to your entire dashboard
Version: 0.2
Author: Tzafrir Rehan
Author URI: http://tzafrir.net/
*/

/*  Copyright 2008  Tzafrir Rehan  (email : tzafrir@tzafrir.net)
			The core of the code is from ozh's Admin Drop Down Menu
			http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

load_plugin_textdomain('wp-bookmark-maker',
						'/wp-content/plugins/wp-bookmark-maker/');

function tz_wpbm_menu() { // function that calls the option menu function
    if (function_exists('add_options_page')) {
        add_options_page(__('Bookmark generator', 'wp-bookmark-maker'),
			__('Wordpress Bookmark Maker', 'wp-bookmark-maker'), 8, 
							basename(__FILE__), 'tz_wpbm_menupage');
    }
 }

function tz_wpbm_menupage() {
?>

<div class="wrap">
<h2><a href="<?php echo($_SERVER['url']) ?>?tz_wpbm_file=1"><?php _e('Click to download a bookmark folder', 'wp-bookmark-maker');?></a></h2>
<p><?php _e('Import the above file using the Import... function in Firefox\'s Organize Bookmarks menu.', 'wp-bookmark-maker'); ?></p><p><?php _e('<a href="http://mozilla.gunnars.net/firefox_bookmarks_tutorial.html#importing_and_exporting_bookmarks">More help here.</a>', 'wp-bookmark-maker');?></p>
</div>

<?php
}

function wp_tz_ozh_adminmenu_build () {
	global $menu, $submenu, $plugin_page, $pagenow;
	
	/* Most of the following garbage are bits from admin-header.php,
	 * modified to populate an array of all links to display in the menu
	 */
	 
	$self = preg_replace('|^.*/wp-admin/|i', '', $_SERVER['PHP_SELF']);
	$self = preg_replace('|^.*/plugins/|i', '', $self);
	
	/* Make sure that "Manage" always stays the same. Stolen from Andy @ YellowSwordFish */
	$menu[5][0] = __("Write");
	$menu[5][1] = "edit_posts";
	$menu[5][2] = "post-new.php";
	$menu[10][0] = __("Manage");
	$menu[10][1] = "edit_posts";
	$menu[10][2] = "edit.php";	
	
	//get_admin_page_parent();
	
	$altmenu = array();
	
	/* Step 1 : populate first level menu as per user rights */
	foreach ($menu as $item) {
		// 0 = name, 1 = capability, 2 = file
		if ( current_user_can($item[1]) ) {
			if ( file_exists(ABSPATH . "wp-content/plugins/{$item[2]}") )
				$altmenu[$item[2]]['url'] = get_settings('siteurl') . "/wp-admin/admin.php?page={$item[2]}";			
			else
				$altmenu[$item[2]]['url'] = get_settings('siteurl') . "/wp-admin/{$item[2]}";

			if (( strcmp($self, $item[2]) == 0 && empty($parent_file)) || ($parent_file && ($item[2] == $parent_file)))
			$altmenu[$item[2]]['class'] = " class='current'";
			
			$altmenu[$item[2]]['name'] = $item[0];

			/* Windows installs may have backslashes instead of slashes in some paths, fix this */
			$altmenu[$item[2]]['name'] = str_replace(chr(92),chr(92).chr(92),$altmenu[$item[2]]['name']);
		}
	}
	
	/* Step 2 : populate second level menu */
	foreach ($submenu as $k=>$v) {
		foreach ($v as $item) {
			if (array_key_exists($k,$altmenu) and current_user_can($item[1])) {
				
				// What's the link ?
				$menu_hook = get_plugin_page_hook($item[2], $k);

				if (file_exists(ABSPATH . "wp-content/plugins/{$item[2]}") || ! empty($menu_hook)) {
					list($_plugin_page,$temp) = explode('?',$altmenu[$k]['url']);
					$link = $_plugin_page.'?page='.$item[2];
				} else {
					$link =  get_settings('siteurl') . "/wp-admin/" . $item[2];
				}
				
				/* Windows installs may put backslashes instead of slashes in paths, fix this */
				$link = str_replace(chr(92),chr(92).chr(92),$link);
				
				$altmenu[$k]['sub'][$item[2]]['url'] = $link;
				
				// Is it current page ?
				$class = '';
				if ( (isset($plugin_page) && $plugin_page == $item[2] && $pagenow == $k) || (!isset($plugin_page) && $self == $item[2] ) ) $class=" class='current'";
				if ($class) {
					$altmenu[$k]['sub'][$item[2]]['class'] = $class;
					$altmenu[$k]['class'] = $class;
				}
				
				// What's its name again ?
				$altmenu[$k]['sub'][$item[2]]['name'] = $item[0];
			}
		}
	}
	
	// Dirty debugging: break page and dies
	/**
	echo "</ul><pre style='font-size:9px'>";
	echo '__MENU ';print_r($menu);
	echo 'SUBMENU ';print_r($submenu);
	echo 'ALTMENU ';print_r($altmenu);
	die();
	/**/
	
	// Clean debugging: prints after footer
	/**
	global $wpdb;
	$wpdb->wp_tz_ozh_adminmenu_neat_array = "<pre style='font-size:80%'>Our Oh-So-Beautiful-4-Levels-".htmlentities(print_r($altmenu,true))."</pre>";
	add_action('admin_footer', create_function('', 'global $wpdb; echo $wpdb->wp_tz_ozh_adminmenu_neat_array;')); 
	/**/

	return ($altmenu);
}
function wp_tz_ozh_adminmenu() {
	$menu = wp_tz_ozh_adminmenu_build();
		// Create our bookmarks.html
	$tz_ozh_menu = '<!DOCTYPE NETSCAPE-Bookmark-file-1>
<!-- This is an automatically generated file.
     It will be read and overwritten.
     DO NOT EDIT! -->
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<TITLE>Bookmarks</TITLE>
<H1 LAST_MODIFIED="'. time() .'">Bookmarks</H1>

<DT><H3>'. __('WordPress admin Dashboard', 'wp-bookmark-maker').' - '. get_settings('blogname') . '</H3>
		<DL><p>
			'; // bookmark head
	
	foreach ($menu as $k=>$v) {
		$url 	= $v['url'];
		$name 	= $k;
		$anchor = $v['name'];
		$class	= $v['class'];

		if (is_array($v['sub'])) $tz_ozh_menu .= "<DT><H3>$anchor</H3>\n\t\t<DL><p>"; 
		else $tz_ozh_menu .= "\t<DT><A href=\"$url\">$anchor</A>";
		if (is_array($v['sub'])) {
			
			$ulclass='';
			if ($class) $ulclass = " class='ulcurrent'";
			$tz_ozh_menu .= "\n\t\t\n";

			foreach ($v['sub'] as $subk=>$subv) {
				$suburl = $subv['url'];
				$subanchor = $subv['name'];
				$subclass='';
				if (array_key_exists('class',$subv)) $subclass=$subv['class'];
				$tz_ozh_menu .= "\t\t\t<DT><A href=\"$suburl\">$subanchor</A>\n";
			}
			$tz_ozh_menu .= "\t</DL><p>\n";
		}
		$tz_ozh_menu .="\n";
	}
	$tz_ozh_menu = preg_replace('/ <span id=\'awaiting-mod.*H3>/', '</H3>', $tz_ozh_menu);
	echo $tz_ozh_menu;
	
}

function tz_wpbm_html() {
	// In a wonderful turn of events, this code runs if an admin page's url has '?tz_wpbm_file=1' on it.
	
	// Let's make them download the file instead of viewing it
	header("Content-Type: text/html");
	header('Content-Disposition: attachment; filename="wp-bookmark.html"');
	// Spill it out!
	wp_tz_ozh_adminmenu();
	// Actually we're cheating, and this all happens in the loading phase of an admin page!
	// So make sure nobody ever knows about this.
	die();
}

// This is orthodox:
add_action('admin_menu', 'tz_wpbm_menu');

// This isn't.
if (is_admin() && $_GET[tz_wpbm_file] == '1') {
add_action('admin_menu', 'tz_wpbm_html');
}

?>
