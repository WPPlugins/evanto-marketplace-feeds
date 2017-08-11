<?php
/*
Plugin Name: Envato Marketplace Feeds
Plugin URI: http://www.gilbertpellegrom.co.uk/projects/envato-marketplace-feeds/
Description: Lets you add feeds from the Envato Marketplaces to your blog. Requires PHP 5+ to run.
Version: 0.3
Author: Gilbert Pellegrom
Author URI: http://www.gilbertpellegrom.co.uk

==== VERSION HISTROY ====
v0.1 	- Release Version
v0.2 	- Bug fix: Activation not creating table.
v0.3 	- Added support for thumbnails and images.

==== COPYRIGHT ====
Copyright 2009  Gilbert Pellegrom  (email : drummermanny@gmail.com)

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
====================
*/

//Define globals
$api_version = 'edge';
//Wordpress hooks
add_action('admin_menu', 'emf_admin_functions');
register_activation_hook(__FILE__, 'emf_activate');

//Initial activation actions
function emf_activate()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "emf";
	
	//Create our database
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  marketplace varchar(50) NOT NULL,
			  type varchar(50) NOT NULL,
			  category varchar(50) NOT NULL,
			  apikey varchar(150) NOT NULL,
			  username VARCHAR(50) NOT NULL,
			  private TINYINT(1) NOT NULL,
			  UNIQUE KEY id (id)
			);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		//Incase we need to upgrade add version reference
		add_option("emf_db_version", "0.1");
	}
	
	//Add default options
	add_option("emf_username", "");
	add_option("emf_apikey", "");
}

//Add any admin functions
function emf_admin_functions() {	
	//Add options page
	add_options_page('EMF Settings', 'EMF Settings', 8, __FILE__, 'emf_settings');
}

//EMF settings page
function emf_settings(){
	global $wpdb;
	global $api_version;
	$update_message = '';
	$table_name = $wpdb->prefix . "emf";
	
	//Delete Feed
	if(isset($_GET['remove']) && $_GET['remove'] != ''){
		//SQL delete statement
		$delete = "DELETE FROM " . $table_name . " WHERE id='". $wpdb->escape($_GET['remove']) ."'";

		$results = $wpdb->query( $delete );
		$update_message = 'Successfully removed feed';
	}
	
	//Add Feed
	if(isset($_POST['marketplace'])){
		//Remember details for next time
		if($_POST['username'] != '') update_option("emf_username", $wpdb->escape($_POST['username']));
		if($_POST['apikey'] != '')	update_option("emf_apikey", $wpdb->escape($_POST['apikey']));
		
		//Is it a private feed?
		$private = 0;
		if($_POST['type'] == 'vitals' || $_POST['type'] == 'account' || $_POST['type'] == 'earnings-and-sales-by-month' || 
			$_POST['type'] == 'statement' || $_POST['type'] == 'recent-sales'){
				$private = 1;
		}
	
		//SQL insert statement
		$insert = "INSERT INTO " . $table_name . " (marketplace, type, category, apikey, username, private) " .
			"VALUES ('" . $wpdb->escape($_POST['marketplace']) . "','" . $wpdb->escape($_POST['type']) . "','" . $wpdb->escape($_POST['category']) . "','" . $wpdb->escape($_POST['apikey']) . "','" . $wpdb->escape($_POST['username']) . "','" . $private . "')";

		$results = $wpdb->query( $insert );
		$update_message = 'Successfully added a feed';
	}
	
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>Envato Marketplace Feed Settings</h2>
		<? 	if($update_message != '') echo '<div class="updated"><p><strong>'. $update_message .'</strong></p></div>'; ?>
		<form method="post" action="<?=$_SERVER['PHP_SELF'].'?page=evanto-marketplace-feeds/emf.php'?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Select A Marketplace</th>
					<td>
						<select name="marketplace">
						  <option value="audiojungle">AudioJungle</option>
						  <option value="flashden">FlashDen</option>
						  <option value="graphicriver">GraphicRiver</option>
						  <option value="themeforest">ThemeForest</option>
						  <option value="videohive">VideoHive</option>
						</select> 
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Select A Feed Type</th>
					<td>
						<select name="type" id="type"> 
						<optgroup label="Public Feeds">
						  <option value="active-threads">Active Threads</option>
						  <option value="blog-posts">Blog Posts</option>
						  <option value="new-files">New Files</option>
						  <option value="new-files-from-user">New Files from User</option>
						  <option value="number-of-files">Number of Files</option>
						  <option value="popular">Popular</option>
						  <option value="random-new-files">Random New Files</option>
						<optgroup label="Private Feeds (Requires API Key)">
						  <option value="vitals">Vitals</option>
						  <option value="account">Account</option>
						  <option value="earnings-and-sales-by-month">Earning and Sales per Month</option>
						  <option value="statement">Statement</option>
						  <option value="recent-sales">Recent Sales</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="category">Category<label></th>
					<td>
						<input type="text" name="category" id="category" class="regular-text" />
						<br /><span class="setting-description">Only required for the New Files feed</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="apikey">API Key<label></th>
					<td>
						<input type="text" name="apikey" id="apikey" class="regular-text" /> <? if(get_option('emf_apikey') != ''){ echo 'Last used API key: <strong>'. get_option('emf_apikey') .'</strong>'; } ?>
						<br /><span class="setting-description">Only required for private feeds</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="username">Username<label></th>
					<td>
						<input type="text" name="username" id="username" class="regular-text" />
						<? if(get_option('emf_username') != ''){ echo 'Last used Username: <strong>'. get_option('emf_username') .'</strong>'; } ?>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="Submit" value="Add Feed" class="button-primary" /></p>
		</form>
		
		<h2>Current Feeds</h2>
		<table class="widefat" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th><th>Feed Description</th><th width="100">Remove Feed</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$feeds = $wpdb->get_results("SELECT * FROM ". $table_name ." ORDER BY id ASC");
				foreach($feeds as $feed){ ?>
				<tr>
					<td><? echo $feed->id; ?></td>
					<td>Gets <strong><? echo $feed->type; ?></strong>
					<? if($feed->private == 0) echo ' from <strong>'. $feed->marketplace .'</strong>'; ?>
					<? if($feed->category != '') echo ' in the category <strong>'. $feed->category .'</strong>'; ?>
					<? if($feed->username != '') echo ' with the username <strong>'. $feed->username .'</strong>'; ?>
					<? if($feed->apikey != '') echo ' and the API key <strong>'. $feed->apikey .'</strong>'; ?>
					<br />
					<?
					//Create the feed URL
					$url = 'http://marketplace.envato.com/api/'. $api_version .'/';
					if($feed->apikey == '' && $feed->username == '' && $feed->category == ''){
						$url .= $feed->type .':'. $feed->marketplace .'.xml';
					}
					elseif($feed->apikey == '' && $feed->username == '' && $feed->category != ''){ 
						$url .= $feed->type .':'. $feed->marketplace .','. $feed->category .'.xml';
					}
					elseif($feed->apikey == '' && $feed->username != ''){ 
						$url .= $feed->type .':'. $feed->username .','. $feed->marketplace .'.xml';
					}
					elseif($feed->apikey != '' && $feed->username != ''){ 
						$url .= $feed->username .'/'. $feed->apikey .'/'. $feed->type .'.xml';
					}
					else{ 
						$url = 'Invalid URL';
					}
					?>
					You can see the raw data at <a href="<? echo $url; ?>" target="_blank"><? echo $url; ?></a>
					</td>
					<td><a href="<?=$_SERVER['PHP_SELF'].'?page=evanto-marketplace-feeds/emf.php&remove='.$feed->id ?>">Remove</a></td>
				</tr>
				<? } ?>
			</tbody>
		</table>
		<br /><br />
	</div>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#type').change(function () {
			//Inputs are disabled by default
			jQuery("input[@name='category']").attr("disabled", true).val('Disabled for this feed type'); 
			jQuery("input[@name='apikey']").attr("disabled", true).val('Disabled for this feed type'); 
			jQuery("input[@name='username']").attr("disabled", true).val('Disabled for this feed type');
			
			var page = jQuery('#type :selected').val();
			if(page == 'new-files'){
				jQuery("input[@name='category']").removeAttr("disabled").val(''); 
			}
			if(page == 'new-files-from-user'){
				jQuery("input[@name='username']").removeAttr("disabled").val(''); 
			}
			if(page == 'vitals' || page == 'account' || page == 'earnings-and-sales-by-month' || 
				page == 'statement' || page == 'recent-sales'){
				jQuery("input[@name='apikey']").removeAttr("disabled").val(''); 
				jQuery("input[@name='username']").removeAttr("disabled").val(''); 
			}
        }).change();
	});
	</script>
	<?
}

function emf_getfeed($id = 0, $returnData = false){
	global $wpdb;
	global $api_version;
	$table_name = $wpdb->prefix . "emf";
	
	//Get our database info
	$feed = $wpdb->get_row("SELECT * FROM ". $table_name ." WHERE id='". $wpdb->escape($id) ."'");
	if(sizeof($feed) == 0){
		echo 'EMF Error: Invalid feed id';
		return null;
	}
	
	//Build the url
	$url = 'http://marketplace.envato.com/api/'. $api_version .'/';
	if($feed->apikey == '' && $feed->username == '' && $feed->category == ''){
		$url .= $feed->type .':'. $feed->marketplace .'.xml';
	}
	elseif($feed->apikey == '' && $feed->username == '' && $feed->category != ''){ 
		$url .= $feed->type .':'. $feed->marketplace .','. $feed->category .'.xml';
	}
	elseif($feed->apikey == '' && $feed->username != ''){ 
		$url .= $feed->type .':'. $feed->username .','. $feed->marketplace .'.xml';
	}
	elseif($feed->apikey != '' && $feed->username != ''){ 
		$url .= $feed->username .'/'. $feed->apikey .'/'. $feed->type .'.xml';
	}
	else{ 
		return null;
	}
	
	//Use cURL to get our feed
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return data rather then echo it
	curl_setopt($ch, CURLOPT_URL, $url); //Pass in our url
	$data = curl_exec($ch);
	curl_close($ch);
	$xml = new SimpleXMLElement($data); //Read the returned XML
	
	//Either return of echo data
	if($returnData){
		return $xml;
	} else {
		$html = '';
		if($feed->type == 'active-threads'){ 
			$xmlNodes = $xml->{'active-threads'}->{'active-thread'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->title .'</a>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'blog-posts'){ 
			$xmlNodes = $xml->{'blog-posts'}->{'blog-post'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->title .'</a><br />';
				$html .= '<span>Posted on '. emf_parsedate($element->{'posted-at'}) .'</span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'new-files'){ 
			$xmlNodes = $xml->{'new-files'}->{'new-file'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->item .'</a><br />';
				$html .= '<img src="'. $element->thumbnail .'" alt="'. $element->item .'"><br />';
				$html .= '<span>By <a href="http://flashden.net/user/'. $element->user .'">'. $element->user .'</a></span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'new-files-from-user'){ 
			$xmlNodes = $xml->{'new-files-from-user'}->{'new-files-from-user'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->item .'</a><br />';
				$html .= '<img src="'. $element->thumbnail .'" alt="'. $element->item .'"><br />';
				$html .= '<span>Costs $'. $element->cost .'</span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'number-of-files'){ 
			$xmlNodes = $xml->{'number-of-files'}->{'number-of-file'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><span>'. $element->{'number-of-files'} .'</span> in <a href="'. $element->url .'">'. $element->category .'</a>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'popular'){ 
			$xmlNodes = $xml->{'popular'}->{'items-last-week'}->{'items-last-week'}; //TODO Include other popular selections
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->item .'</a><br />';
				$html .= '<img src="'. $element->thumbnail .'" alt="'. $element->item .'"><br />';
				$html .= '<span>'. $element->sales .' sales</span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'random-new-files'){ 
			$xmlNodes = $xml->{'random-new-files'}->{'random-new-file'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="'. $element->url .'">'. $element->item .'</a><br />';
				$html .= '<img src="'. $element->thumbnail .'" alt="'. $element->item .'"><br />';
				$html .= '<span>Costs $'. $element->cost .'</span>';
			}
			$html .= '</ul>';
		}
		
		if($feed->type == 'vitals'){ 
			$xmlNodes = $xml->{'vitals'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li><a href="http://flashden.net/user/'. $element->username .'">'. $element->username .'</a> <span>$'. $element->balance .'</span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'account'){ 
			$xmlNodes = $xml->{'account'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li>'. $element->firstname .' '. $element->surname .'</a><ul>';
				$html .= '<li>Total Earnings: <span>$'. $element->{'total-earnings'} .'</span></li>';
				$html .= '<li>Total Deposits: <span>$'. $element->{'total-deposits'} .'</span></li>';
				$html .= '<li>Balance: <span>$'. $element->{'balance'} .'</span></li>';
				$html .= '<li>Commission Rate: <span>$'. $element->{'current-commission-rate'} .'</span></li>';
				$html .= '<li>Country: <span>'. $element->{'country'} .'</span></li>';
				$html .= '<li><img src="'. $element->image .'" alt="'. $element->firstname .' '. $element->surname .'"></li>';
				$html .= '</ul></li>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'earnings-and-sales-by-month'){ 
			$xmlNodes = $xml->{'earnings-and-sales-by-month'}->{'earnings-and-sales-by-month'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li>'. emf_parsedate($element->month, 1) .': <span>'. $element->sales .' sales</span>, <span>$'. $element->earnings .'</span>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'statement'){ 
			$xmlNodes = $xml->{'statement'}->{'statement'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li>'. $element->description .'<ul>';
				$html .= '<li>Action: <span>'. str_replace('_', ' ', $element->kind) .'</span></li>';
				$html .= '<li>Amount: <span>$'. $element->amount .'</span></li>';
				$html .= '<li>Occured At: <span>'. emf_parsedate($element->{'occured-at'}, 2) .'</span></li>';
				$html .= '</ul></li>';
			}
			$html .= '</ul>';
		}
		if($feed->type == 'recent-sales'){ 
			$xmlNodes = $xml->{'recent-sales'}->{'recent-sale'};
			$html = '<ul id="emf-'. $feed->type .'">';
			foreach($xmlNodes as $element){
				$html .= '<li>Sold '. $element->item .' for $'. $element->amount .'<br />';
				$html .= '<span>At '. emf_parsedate($element->{'sold-at'}, 2) .'</span></li>';
			}
			$html .= '</ul>';
		}
		
		echo $html;
	}	
}

function emf_parsedate($date, $type = 0){
	if($type == 0){
		list($year, $month, $day) = split('[/.-]', $date);
		return "$day/$month/$year";
	} 
	if($type == 1) {
		list($first, $second) = split('T', $date);
		list($year, $month, $day) = split('[/.-]', $first);
		$month = date("F", mktime(0, 0, 0, $month, 10)); 
		return "$month $year";
	}
	if($type == 2) {
		list($first, $second) = split('T', $date);
		list($year, $month, $day) = split('[/.-]', $first);
		list($hour, $minute, $second) = split('[:+]', $second);
		return "$hour:$minute, $day/$month/$year";
	}
}
?>