<?php
/**
 * @package wblg-series-core-admin
 * @version 1.0
 */
/*
Plugin Name: WBLG series Core Admin
Plugin URI: http://wordpress.org/extend/plugins/wblg-series-core-admin/
Version: 1.0
Description: Core Admin for WebLegend Series plugins.
Author: Alain Bariel
Author URI: http://www.la-dame-du-lac.com/
Text Domain: wblg-series-core-admin
Domain Path: /langs/
License: GPL2
*/
/*  Copyright 2012  Alain Bariel  (email : lancelot@la-dame-du-lac.com)

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
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* What to do when the plugin is activated? */
if ( function_exists('register_activation_hook') ) {
	register_activation_hook(__FILE__, 'wblg_series_core_admin_install');
}

/* What to do when the plugin is deactivated? */
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'wblg_series_core_admin_uninstall');
}
function wblg_series_core_admin_alert() {
	// admin notice
	if( $version_id ) {
		$plugin=plugin_basename( __FILE__ );
		if ( is_plugin_active($plugin) ) {
			unset($_GET['activate']);
			deactivate_plugins($plugin, true);
			do_action( 'wblg-series-messages', 'no-sitewide');
		}
	}
}
if($_GET['activate']) {
	add_action('admin_notices', 'wblg_series_core_admin_alert');
}
function wblg_series_core_admin_install() {
	// things to do
}
function wblg_series_core_admin_uninstall() {
	// things to do
}

function wblg_series_admin_menu() {
	// load language
	load_plugin_textdomain('wblg-series-core-admin', false, dirname( plugin_basename( __FILE__ ) ).'/langs/' );
	
	define('PHP_VERSION_REQUIRED_ID',50300);
	define('PHP_VERSION_REQUIRED_STR','5.3.0');
	
	$capability='manage_options';
	//fn vars: add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page('WebLegend Series', 'WbLgnd Series', $capability, 'wblg-series-core-admin', 'wblg_series_admin_page', plugins_url('images/icon16-weblegend-blue.png', __FILE__) );
	// to do: remove first submenu named as 'WbLgnd Series' from add_menu_page() and name it 'Dashboard'...
	
	$wblg_plugin_found=wblg_series_admin_submenu();
	foreach($wblg_plugin_found as $tab_plugn=>$title_plugn) {
		//fn vars: add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page('wblg-series-core-admin', 'Wblg series: '.$title_plugn, $title_plugn, $capability, $tab_plugn, 'wblg_series_admin_page');
	}
}
add_action('admin_menu', 'wblg_series_admin_menu');

function wblg_series_admin_submenu() {
	$wblg_plugin_found=array();
	/**
	 here test dir plugin called : wblg-series-...
	**/
	$all_plgn=get_plugins();	
	foreach($all_plgn as $plgn_script => $plgn_details) {
		if(substr($plgn_script,0,12) == 'wblg-series-' && strpos($plgn_script, 'wblg-series-core-admin') === false ) {
			if(PHP_VERSION_ID < PHP_VERSION_REQUIRED_ID) {
				$lastpart = strstr($plgn_script, '/');
				$plgn_dir = str_replace($lastpart,'',$plgn_script);
			} else {
				$plgn_dir = strstr($plgn_script, '/', true);
			}
			$plgn_name= str_replace('WBLG series ','',$plgn_details['Name']);
	 		$wblg_plugin_found[$plgn_dir]=trim($plgn_name);
		}
	}
	return $wblg_plugin_found;
}

function wblg_series_admin_js_css() {
	wblg_series_load_protoscript();
	wp_register_style( 'wblg_series_core_css', plugins_url('wblg-series-core-admin.css', __FILE__) );
	wp_enqueue_style( 'wblg_series_core_css' );
	wp_register_script( 'wblg_series_core_js', plugins_url('wblg-series-core-admin.js', __FILE__)  );
	wp_enqueue_script( 'wblg_series_core_js' );
}	
add_action( 'admin_enqueue_scripts', 'wblg_series_admin_js_css', 1 );
	
function wblg_series_admin_page () {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.' ) );
	}
	$wblg_home_page=array('wblg-series-core-admin'=>'WebLegend Series');
	// here test dir plugin
	$wblg_plugin_found=wblg_series_admin_submenu();
 	$wblg_pages=array_merge($wblg_home_page,$wblg_plugin_found);
	?>	
	<div class="wrap">		
			<?php screen_icon('weblegend'); ?>
			<h3 class="nav-tab-wrapper">
				<?php
				foreach($wblg_pages as $tab_page=>$title_page) {
					$tab_active='';
					if($_GET["page"]==$tab_page) {
						$tab_active=' nav-tab-active';
					}
					//$tab_url = admin_url().'admin.php?page='.$tab_page;
					$tab_url = admin_url('admin.php?page='.$tab_page );
					echo "<a href=\"".$tab_url."\" class=\"nav-tab".$tab_active."\">".$title_page."</a>";	
				}
				?>
			</h3>
		<!-- content -->
		<div id="wblg-content">
			<?php
				// page plugins
				$wblg_plugn_page= WP_PLUGIN_DIR."/".$_GET["page"]."/".$_GET["page"];
				$wblg_admin_page= $wblg_plugn_page."-admin.php";				
				$plugin_details=get_plugin_data($wblg_plugn_page.'.php');
				echo "<div class=\"pluginfo\">";
					echo "<div>".$plugin_details['Title'].", version ".$plugin_details['Version']."</div>";
					echo "<div>".$plugin_details['Description']."</div>";
					if(PHP_VERSION_ID < PHP_VERSION_REQUIRED_ID) {
						$msg_required =sprintf( __('Your PHP version is: %1$s installing the %2$s is highly recommended','wblg-series-core-admin'), PHP_VERSION, PHP_VERSION_REQUIRED_STR );
						$msg_required.="<hr /><i>".__('Add the following line in your htaccess at the root of the site can solve the problem.','wblg-series-core-admin')."</i>";
						//$msg_required.="<br /><code>AddHandler application/x-httpd-php53 .php</code>";
						$msg_required.="<br /><input type=\"text\" class=\"php-required\" value=\"AddHandler application/x-httpd-php53 .php\" />";
						echo "<div>".$msg_required."</div>"; 
					}
				echo "</div>";
			
				if($_GET["page"]=='wblg-series-core-admin') {
					// page credits
					$Url="http://www.la-dame-du-lac.com/core/";
					$ch=curl_init();
					if($ch) {
						$wblg_curl_page=wblg_series_curlpage($ch,$Url);
						$wblg_curl_content=$wblg_curl_page["html"];
						$wblg_curl_encoding=$wblg_curl_page["charset"]; // if is empty ?
					} else {
						$wblg_curl_content=false;
					}
					curl_close($ch);
					//
					if($wblg_curl_content) {
						$Dom=new DOMDocument("1.0","UTF-8");
						$Dom->loadHTML($wblg_curl_content);
						$Cnt=$Dom->getElementById('core');
						//$Cnt->nodeValue;
						echo get_inner_html($Cnt);
					} else {
						echo "<h3>". __('Error') ."</h3>";
						echo "<p><a href=\"".$Url."\">".sprintf( __( 'More information about %s' ), $Url )."</a></p>";
					}
				} else {
					if (file_exists($wblg_admin_page)) {
						include_once($wblg_admin_page);	
					} else {
			    		echo "To come...";
					}
				}		
			?>	
		</div>	<!-- / wblg-content -->
	</div> <!-- / wrap -->
	<?php
}

function get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 
    return $innerHTML;
}

function wblg_series_curlpage($ch,$Url) {
	//
	curl_setopt($ch, CURLOPT_URL, $Url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	/*
	curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE); 
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile"); 
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); 
	curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
	*/
	//curl_setopt($ch, CURLOPT_HEADER, 0);
	//curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	/*** user agent ***/
	$agent_select="official"; // scooter|random|official
	if($agent_select=="scooter") {
		//method scooter agent
		$agent='wp-wblg-series';
	} else if($agent_select=="random") {
		//method random agent
		$agents = array(
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1",
		"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4",
		"Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)",
		"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1"
		);
		$agent=$agents[array_rand($agents)];
	} else {
		//method official agent
		$agent=$_SERVER["HTTP_USER_AGENT"];
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	
	/*** headers ***/
	if(	$agent_select=="random") { $agent_select="scooter"; }
	
	$header_official["Accept"]=$_SERVER["HTTP_ACCEPT"]; //the Accept: header from the current request, if there is one.
	$header_scooter["Accept"]="text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	
	$header_official["Accept-Charset"]=$_SERVER["HTTP_ACCEPT_CHARSET"]; //the Accept-Charset: header from the current request, if there is one.
	$header_scooter["Accept-Charset"]="ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	
	$header_official["Accept-Encoding"]=$_SERVER["HTTP_ACCEPT_ENCODING"]; //the Accept-Encoding: header from the current request, if there is one.
	$header_scooter["Accept-Encoding"]="gzip";
	
	$header_official["Accept-Language"]=$_SERVER["HTTP_ACCEPT_LANGUAGE"]; //the Accept-Language: header from the current request, if there is one.
	$header_scooter["Accept-Language"]="en-us,en;q=0.5";
	
	$header_official["Connection"]=$_SERVER["HTTP_CONNECTION"]; //the Connection: header from the current request, if there is one.
	$header_scooter["Connection"]="keep-alive";
	
	$header_official["Keep-Alive"]="300";
	$header_scooter["Keep-Alive"]="300";
	
	$header_official["Cache-Control"]="max-age=0";
	$header_scooter["Cache-Control"]="max-age=0";
/*		
	$header_official["Host"]=$_SERVER["HTTP_HOST"]; //the Host: header from the current request, if there is one.
	$header_scooter["Host"]="";
*/
	$header_official["Pragma"]="";
	$header_scooter["Pragma"]="";
	
	//set the header params
	if($header_select=='scooter') {
		$header_select=$header_scooter;
	} else {
		$header_select=$header_official;
	}
	foreach ($header_select as $hk => $hv) {
		$header[]=$hk.": ".$hv;
	}
	// model
	//$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
	//$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3";
	//$header[] = "Accept-Encoding: gzip,deflate,sdch";
	//$header[] = "Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4";
	//$header[] = "Connection: keep-alive";
	//$header[] = "Keep-Alive: 300";
	//$header[] = "Cache-Control: max-age=0";
	//$header[] = "Host: localhost";
	//$header[] = "Pragma: ";

	//assign to the curl request.
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	/*** end headers ***/
	
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$page_res=curl_exec ($ch);
	$page_nfo=curl_getinfo($ch);
	$cnType=explode("=",$page_nfo["content_type"]);
	$encoding=strtoupper($cnType[1]);
	return array("html"=>$page_res,"charset"=>$encoding);
}

// core functions...
function wblg_series_load_protoscript() {
	$wp_version=$GLOBALS['wp_version'];
	wp_enqueue_script('prototype');
	wp_enqueue_script('scriptaculous');
}

function wblg_series_scan_dir($src_dir) {	
	$files_array=array();
//	$path_src_dir=plugin_dir_path(__FILE__).'../'.$src_dir;
	$d=dir($src_dir);
	$i=0;
	$srcexclue=array(".","..",".htaccess","_htaccess","Icon",".DS_Store",".FBCLockFolder");
	while( $src=$d->read() ) {
		if( !in_array($src,$srcexclue) ) {
			$files_array[$i]=$src;
			$i++;
		}
	}
	$d->close();
	sort($files_array);
	return $files_array;
}
function wblg_series_messages($label,$css="updated") {
	// $css = [error|updated|updated fade]
	$actionplugin='';
	$Msgs=array(
		"php-version"=>"php version",
		"no-sitewide"=>__( 'Plugin install failed.' )."&nbsp;".__('Multisite support is not enabled.'),
		"no-plugin"=>__( 'Plugin <strong>deactivated</strong>.' )
	);
	if( !isset($Msgs[$label]) )
		$Msgs[$label]=$label;
	if ( !current_user_can('activate_plugins') && $label=='no-plugin')
 		$actionplugin="<p>".__( 'You do not have sufficient permissions to activate plugins for this site.')."</p>";
	?>
	<div id="message" class="<?php echo $css; ?>">
		<p><strong><?php echo $Msgs[$label]; ?></strong></p><?php echo $actionplugin; ?>
	</div>
	<?php
}
add_action( 'wblg-series-messages', 'wblg_series_messages',10,2);
?>