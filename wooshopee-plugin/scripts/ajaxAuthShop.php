<?php
error_reporting(0);
require_once( plugin_dir_path( __FILE__ ) . 'classes/classShopeeAuth.php' );
// System Configs
$host = "https://partner.shopeemobile.com";
$partnerID = 2006716;
$partnerKey = "545a706352504c6e5042417879624366525444705a56704d4963444a4f45596a";
$redirectURI = "http://localhost/shopee/newhome.php";

session_start();
$_SESSION['created'] = time();

$arrConfig = array(
            // 'host' => $host,
            'partner_id' => $partnerID,
            'partner_key' => $partnerKey,
            'redirect_uri' => $redirectURI
        );

$client = new shopeeAuth( $arrConfig );
// $client->setHost( $host );
$urlResult = $client->authShop();
echo $urlResult;
