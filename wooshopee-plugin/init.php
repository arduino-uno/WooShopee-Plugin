<?php
/*
Plugin Name: WooShopee Plugin
Plugin URI: https://wordpress.org/plugins/wooshopee-plugin
Description: Synchronize Shops and Items, process Orders and Payments & more!
Version: 3.0.0
Author: nath4n
Author URI: https://profiles.wordpress.org/nath4n
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: wporg
Domain Path: /languages
*/

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
/* Set Date & Time to regional timezone */
date_default_timezone_set('Asia/Jakarta');
/* Add all classess */
require_once( plugin_dir_path( __FILE__ ) . 'classes/classShopeeAuth.php' );
require_once( plugin_dir_path( __FILE__ ) . 'classes/classShopeeShop.php' );
require_once( plugin_dir_path( __FILE__ ) . 'classes/classShopeeProduct.php' );

/* System Configs */
$host = "https://partner.shopeemobile.com";
$partnerID = 2006716;
$partnerKey = "545a706352504c6e5042417879624366525444705a56704d4963444a4f45596a";
$redirectURI = get_site_url() . "/wp-admin/admin.php?page=wooShopee_main_page";
/* Request parameters */
$code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
$shopID = filter_input(INPUT_GET, 'shop_id', FILTER_SANITIZE_STRING);
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
/* Gets array settings */
$sets = get_option( 'shopee_auths' );
$getErrStatus = "";
$getErrMessage = "";
$refreshToken = "";

add_action( 'admin_head', function() {
global $sets, $page;

	if ( $page == "wooShopee_main_page" || $page == "wooShopee_products_page") {

		$created = $sets['created'];
		$time_created =  date( 'm/d/Y H:i:s', $created );
		$time_expired = date( 'm/d/Y H:i:s', strtotime( $time_created . ' + 4 hours' ) );
		$current_time = date( 'm/d/Y H:i:s', time() );
			
		$timeExpired = strtotime( $time_expired );
		$currentTime = strtotime( $current_time );
		
		if ( $currentTime >= $timeExpired ) {

			echo "<script>
				jQuery( function( $ ) {
					$( \"<div title=\'WooShopee\'><p>Your access token has expired. Please request a new one.</p></div>\" ).dialog({
						resizable: false,
						draggable: false,
						height: \"auto\",
						width: 400,
						modal: true,
						buttons: {
							Ok: function() {
								$( \"input#add_btn\" ).prop( \"disabled\", false );
								$( this ).dialog( \"close\" );
							}
						}
					});
				});
				</script>";

			delete_option( 'shopee_auths' );
			return true;

		};

		/* Check any existing data settings	 */
		if ( empty($sets['new_code']) || empty($sets['new_access_token']) || empty($sets['new_refresh_token']) ) {			
			shopee_sessions();
		} else {			
			echo "<script>
					jQuery( function( $ ) {
						$( \"input#add_btn\" ).prop( \"disabled\", true );
					});
				</script>";					
		};

	};

}, 10, 2 );

function shopee_sessions() {
	global $wpdb, $sets, $host, $code, $shopID, $partnerID, $partnerKey, $getErrStatus, $getErrMessage, $refreshToken;
		
	// update_option for shop_id
	if ( empty($sets['shop_id']) ) {
		$sets['shop_id'] = $shopID;
		update_option( 'shopee_auths', $sets );
	};

	// Sets array settings
	$arrConfig = array(
		'host' => $host,
		'partner_id' => $partnerID,
		'partner_key' => $partnerKey,
		'code' => $code,
		'shop_id' => $shopID
	);

	$client = new shopeeAuth( $arrConfig );
	// Call getTokenShopLevel() function
	$getTokenShopLevel = $client->getTokenShopLevel();
	$getErrStatus = $client->getErrStatus();
	$getErrMessage = $client->getErrMessage();
	$refreshToken = $client->getRefreshToken();
	// update_option for new_code
	$sets['new_code'] = $refreshToken;
	update_option( 'shopee_auths', $sets );		
	// Get all options
	$new_code = $sets['new_code'];
	$shop_id = $sets['shop_id'];
	$created = $sets['created'];
	// Get $newAccessToken & $newRefreshToken
	$client->setCode( $new_code );
	$client->setShopID( $shop_id );
	// Call getAccessTokenShopLevel() function
	$accessTokenShopLevel = $client->getAccessTokenShopLevel();
	$newAccessToken = $client->getNewAccessToken();	
	$newRefreshToken = $client->getNewRefreshToken();
	// update_option for newAccessToken
	$sets['new_access_token'] = $newAccessToken;
	update_option( 'shopee_auths', $sets );
	// update_option for newRefreshToken
	$sets['new_refresh_token'] = $newRefreshToken;
	update_option( 'shopee_auths', $sets );
	return $getTokenShopLevel;
	wp_die();
};

	/* Add JQuery */
	add_action( 'admin_enqueue_scripts', function() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}, 10, 2 );

	add_action( 'admin_menu', function() {
		add_menu_page( 'shopeeApp_adminmenu', 'WooShopee', 'manage_options', 'wooShopee_main_page', 'shopeeApp_page', 'dashicons-products', '10' );
		add_submenu_page( 'wooShopee_main_page', 'Manage Products', 'Manage Products', 'manage_options', 'wooShopee_products_page', 'wooShopee_products_page' );
	}, 10, 2 );

	add_action( 'wp_ajax_action_authShop', function() {
		global $wpdb, $sets, $host, $partnerID, $partnerKey, $redirectURI;
		// nonce check for an extra layer of security, the function will exit if it fails
		if ( !wp_verify_nonce( $_REQUEST['nonce'], "action_nonce")) {
			exit("Woof Woof Woof");
		};

		// update_option for token created timetamp
		if ( empty($sets['created']) ) {
			$sets['created'] = time(); 	/* time now in unix timetamp format */
			update_option( 'shopee_auths', $sets );
		};

		// Sets array settings
		$arrConfig = array(
			'partner_id' => $partnerID,
			'partner_key' => $partnerKey,
			'redirect_uri' => $redirectURI
		);

		$client = new shopeeAuth( $arrConfig );
		$client->setRedirectURL( $redirectURI );
		$urlResult = $client->authShop();
		echo $urlResult;
		wp_die();
	}, 10, 2 );

	/* define the function to be fired for logged out users */
	add_action( 'wp_ajax_nopriv_action_authShop', function() {	
		echo "You must log in to continue ..";
		die();
	}, 10, 2 );

	function wooShopee_products_page() {
		global $sets, $host, $code, $shopID, $partnerID, $partnerKey, $redirectURI;
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		};

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			// Get all Settings
			$new_code = $sets['new_code'];
			$shop_id = $sets['shop_id'];
			$created = $sets['created'];
			// Show all Settings
			echo "<p><b>new_code</b>: {$new_code}<br>";
			echo "<b>shop_id</b>: {$shop_id}<br>";
			$time_created =  date('m/d/Y H:i:s', $created);
			echo "<b>token_created</b>: {$time_created}<br>";
			$time_expired = date('m/d/Y H:i:s', strtotime($time_created . ' + 4 hours') );
			echo "<b>expire_in</b>: {$time_expired}<br>";

			// Sets array settings
			$arrConfig = array(
				'partner_id' => $partnerID,
				'partner_key' => $partnerKey,
				'redirect_uri' => $redirectURI
			);

			$client = new shopeeAuth( $arrConfig );

			$client->setCode( $new_code );
			$client->setShopID( $shop_id );
			$accessTokenShopLevel = $client->getAccessTokenShopLevel();
			echo "<p>";
			var_dump($accessTokenShopLevel);
			echo "<p>";
			$newAccessToken = $client->getNewAccessToken();
			$newRefreshToken = $client->getNewRefreshToken();
			echo "<b>new_access_token</b>: {$newAccessToken}<br>";
			echo "<b>new_refresh_token</b>: {$newRefreshToken}<br>";
			echo "</p>";

			// Show Shop Data & Info
			echo "<p><h2><b>Show Shop Data & Info</b></h2></p>";
			$shopeeShop = new shopeeShop( $arrConfig );
			$shopeeShop->setAccessToken( $newAccessToken );
			$shopeeShop->setShopID( $shop_id );
			$arrShopInfo = $shopeeShop->getShopInfo();
			var_dump($arrShopInfo);
			echo "\n\n";
			echo "<p><h2><b>Show Shop Profile</b></h2></p>";
			$arrProfile = $shopeeShop->getProfile();
			var_dump($arrProfile);
			echo "\n\n";
			// Show Product Info
			echo "<p><h2><b>Show Product Info</b></h2></p>";
			$shopeeProduct = new shopeeProduct( $arrConfig );
			$shopeeProduct->setOffset('0');
			$shopeeProduct->setItemStatus('NORMAL');
			$shopeeProduct->setAccessToken( $newAccessToken );
			$shopeeProduct->setShopID( $shop_id );
			$arrProduct = $shopeeProduct->getItemList();
			var_dump($arrProduct);
			echo "\n\n";
			echo "\n\n";
			// Parsing JSon
			$response = $arrProduct['response'];
			$items = $response['item'];
			var_dump($items);
			foreach($items as $item) {
				$array_id_list[] = $item['item_id'];
			}
			echo "\n";
			$item_id_list = implode (",", $array_id_list);
			// echo $item_id_list;
			// Show Item Data Info
			echo "<p><h2><b>Show Item Base Info</b></h2></p>";
			$shopeeItemInfo = new shopeeProduct( $arrConfig );
			$shopeeItemInfo->setItemIdList( $item_id_list );
			$shopeeItemInfo->setNeedComplaintPolicy( true );
			$shopeeItemInfo->setNeedTaxInfo( true );
			$shopeeItemInfo->setAccessToken( $newAccessToken );
			$shopeeItemInfo->setShopID( $shop_id );
			$arrProductInfo = $shopeeItemInfo->getItemBaseInfo();
			var_dump($arrProductInfo);
			echo "\n\n";
		
		} else {

			echo "<script>
					jQuery( function( $ ) {
						$( \"<div title=\'WooShopee\'><p>WooCommerce is not Active.</p></div>\" ).dialog({
							resizable: false,
							draggable: false,
							height: \"auto\",
							width: 400,
							modal: true,
							buttons: {
								Ok: function() {
									$( this ).dialog( \"close\" );
									window.location.href = '{$redirectURI}';
								}
							}
						});
					});					
					</script>";

		};

	};	

	function shopeeApp_page() {
		global $sets, $arrConfig;
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		};	

		// Get all Settings
		$new_code = $sets['new_code'];
		$shop_id = $sets['shop_id'];
		$created = $sets['created'];
		$access_token = $sets['new_access_token'];
		$refresh_token = $sets['new_refresh_token'];
		?>
		<style>
		.card {
			box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
			transition: 0.3s;
			width: 100%;
			height: auto;
			border-radius: 5px;
		}

		.card:hover {
			box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
		}

		img {
			border-radius: 5px 5px 0 0;
			width: 20%;
			height: auto;
		}

		.float-container {		
			height: 100%;
			width: 100%;
			margin: 10px;
			padding: 10px;			
		}

		.float-left {
			padding: 10px;
			float: left;
			width: 25%;
			height: 280px;
		}
	
		.float-right {
			padding: 10px;		
			float: left;
			width: 50%;
			height: 280px;
		}
		</style>	
		<div class="float-container">
			<h2>Shopee Account Settings</h2>
			<div class="float-left">
				<div class="card">
					<img src="<?php echo plugins_url() . '/wooshopee-plugin/images/shopee_logo.png';?>" alt="Avatar" style="width:100%">
					<div class="container">
						<p><span style="font-family: monospace, monospace;font-weight: bold;font-size: 12px;">The Shopee Open API Client</span></p>
						<input type="submit" class="button" name="add_btn" id="add_btn" value="Add Shopee Account"/>
					</div>
				</div>
			</div>
			<div class="float-right">
				<div class="card">
				<?php
				/* Show all Settings */
				echo "<b>new_code</b>: {$new_code}<br>";
				echo "<b>shop_id</b>: {$shop_id}<br>";
				$time_created =  date('m/d/Y H:i:s', $created);
				echo "<b>token_created</b>: {$time_created}<br>";
				$time_expired = date('m/d/Y H:i:s', strtotime($time_created . ' + 4 hours') );
				echo "<b>expire_in</b>: {$time_expired} <br>";
				echo "<b>access_token</b>: {$access_token} <br>";
				echo "<b>refresh_token</b>: {$refresh_token}";	
				?>
				</div>
			</p>
			</div>
		</div>
		</p>
		<div id="dialog" title="WooShopee Plugin" style="display: none;">
			<p align="text-center">
				<strong>Steps to authorize your account:</strong>
				<p class="font-monospace" align="text-justify">
				1. Click on the “Authorize” button which will redirect you to “open.shopee.com”</br>
				2. On the Shopee authorization page you have to select the Country and then Login with
				your seler panel details</br>
				3. You heve to then cick on “Yes" button to enable access to API</br>
				4. Finally you will be redirected to your site indcicating successful authorization</br>
			</p>
		</div>
		<script type="text/javascript">	
			jQuery( function( $ ) {				
				
				$('input#add_btn').click(function (e) {

					e.preventDefault();
					var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
					var action_nonce = '<?php echo wp_create_nonce( 'action_nonce' ); ?>';
					
					var data = {
						action: 'action_authShop',
						nonce: action_nonce
					};

					jQuery( function( $ ) {
						$( "#dialog" ).dialog({
							resizable: false,
							draggable: false,
							height: "auto",
							width: 400,
							modal: true,
							buttons: {
								Authorize: function() {
									jQuery.ajax({
										type : "post",
										dataType : "text",
										url : ajaxurl,
										data : {action: "action_authShop", nonce: action_nonce},
										success: function(response) {					
											jQuery(location).attr('href', response);
											// console.log(response)						
										}
									});
									$( this ).dialog( "close" );
								},
								Cancel: function () {
									/* otherwise, just close the dialog; the delete event was already interrupted */
									$(this).dialog("close");
								}
							}
						});
					});
					
				});

			});
		</script>	
		<?php
	};