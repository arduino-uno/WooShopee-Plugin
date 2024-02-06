<?php
/*
 * Copyright @ Shopee 2022
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * The Shopee Open API Client
 * https://github.com/arduino-uno/shopee-open-api-client-php
 */

class shopeeAuth
{
    const LIBVER = "2.12.6";
    const USER_AGENT_SUFFIX = "shopee-open-api-client-php/";
    const API_BASE_PATH = 'https://partner.shopeemobile.com';

    private $host, $partner_id, $partner_key, $redirect_uri;
    private $code, $shop_id;
    private $access_token, $refresh_token;
    private $err_status, $err_message, $new_access_token, $new_refresh_token;

    public function __construct( array $config = [] )
    {
        $this->config = array_merge([
            'host' => self::API_BASE_PATH,
            'partner_id' => '',
            'partner_key' => '',
            'redirect_uri' => '',
            'code' => '',
            'shop_id' => '',
            'refresh_token' => ''
        ], $config);

        if (!is_null($this->config['host'])) {
            $this->setHost($this->config['host']);
            unset($this->config['host']);
        };

        if (!is_null($this->config['partner_id'])) {
            $this->setPartnerID($this->config['partner_id']);
            unset($this->config['partner_id']);
        };

        if (!is_null($this->config['partner_key'])) {
            $this->setPartnerKey($this->config['partner_key']);
            unset($this->config['partner_key']);
        };

        if (!is_null($this->config['redirect_uri'])) {
            $this->setRedirectURL($this->config['redirect_uri']);
            unset($this->config['redirect_uri']);
        };

        if (!is_null($this->config['code'])) {
            $this->setCode($this->config['code']);
            unset($this->config['code']);
        };

        if (!is_null($this->config['shop_id'])) {
            $this->setShopID($this->config['shop_id']);
            unset($this->config['shop_id']);
        };

        if (!is_null($this->config['refresh_token'])) {
            $this->setAccessToken($this->config['refresh_token']);
            unset($this->config['refresh_token']);
        };

    }

    public function setHost($host) {
        $this->host = $host;
        return $this->host;
    }

    public function setPartnerID($partner_id) {
        $this->partner_id = $partner_id;
        return $this->partner_id;
    }

    public function setPartnerKey($partner_key) {
        $this->partner_key = $partner_key;
        return $this->partner_key;
    }

    public function setRedirectURL($redirect_uri) {
        $this->redirect_uri = $redirect_uri;
        return $this->redirect_uri;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this->code;
    }

    public function setShopID($shop_id) {
        $this->shop_id = $shop_id;
        return $this->shop_id;
    }
    
    public function getErrStatus() {
        return $this->err_status;
    }

    public function getErrMessage() {
        return $this->err_message;
    }

    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
        return $this->access_token;
    }

    public function getAccessToken() {
        return $this->access_token;
    }
    
    // $this->refresh_token and $this->code are the same values
    public function getRefreshToken() {
        return $this->refresh_token;
    }

    public function getNewAccessToken() {
        return $this->new_access_token;
    }

    public function getNewRefreshToken() {
        return $this->new_refresh_token;
    }

    public function authShop() {
        $host = $this->host;
        $path = "/api/v2/shop/auth_partner";

        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $redirectUrl = $this->redirect_uri;

        $timest = time();
        $baseString = sprintf("%s%s%s", $partnerId, $path, $timest);
        $sign = hash_hmac('sha256', $baseString, $partnerKey);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s", $host, $path, $partnerId, $timest, $sign, $redirectUrl);
        return $url;
    }

    function getTokenShopLevel() {
        $host = $this->host;
        $path = "/api/v2/auth/token/get";

        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer
        $code = $this->code;

        $timest = time();
        $body = array("code" => $code,  "shop_id" => $shopId, "partner_id" => $partnerId);
        $baseString = sprintf("%s%s%s", $partnerId, $path, $timest);
        $sign = hash_hmac('sha256', $baseString, $partnerKey);
        $api_url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $host, $path, $partnerId, $timest, $sign);

        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            $ret = json_decode($response, true);
            $this->err_status = $ret["error"];
            $this->err_message = $ret["message"];
            $this->access_token = $ret["access_token"];
            $this->refresh_token = $ret["refresh_token"];

            if ( $this->err_status !== "" ) {
                throw new Exception( $this->err_status . " - " . $this->err_message );
            };

            if ( curl_errno($curl) ) {
                $ret_error = curl_error($curl);
                throw new Exception( "Request Error: " . $ret_error );
            };

        } catch ( Exception $e ) {

            return "Error description: " . $e->getMessage() . "\n";

        } finally {

            return $ret;

        };

    }

    function getAccessTokenShopLevel() {
        $host = $this->host;
        $path = "/api/v2/auth/access_token/get";

        $partnerId = (int) $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer
        $refreshToken = $this->code;
    
        $timest = time();
        $body = array("partner_id" => $partnerId, "shop_id" => $shopId, "refresh_token" => $refreshToken);
        $baseString = sprintf("%s%s%s", $partnerId, $path, $timest);
        $sign = hash_hmac('sha256', $baseString, $partnerKey);
        $api_url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $host, $path, $partnerId, $timest, $sign);
    
        try {
    
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json'
                ),
            ));
    
            $response = curl_exec($curl);
            $ret = json_decode($response, true);
            $this->err_status = $ret["error"];
            $this->err_message = $ret["message"];
            $this->new_access_token = $ret["access_token"];
            $this->new_refresh_token = $ret["refresh_token"];
    
            if ( $this->err_status !== "" ) {
                throw new Exception( $this->err_status . " - " . $this->err_message );
            };
    
            if ( curl_errno($curl) ) {
                $ret_error = curl_error($curl);
                throw new Exception( "Request Error: " . $ret_error );
            };
    
        } catch ( Exception $e ) {
    
            return "Error description: " . $e->getMessage() . "\n";
    
        } finally {
    
            return $ret;
            
        };
    
    }

};