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

class shopeeShop
{
    const LIBVER = "2.12.6";
    const USER_AGENT_SUFFIX = "shopee-open-api-client-php/";
    const API_BASE_PATH = 'https://partner.shopeemobile.com';

    private $host, $partner_id, $partner_key, $redirect_uri;
    private $code, $shop_id;
    private $access_token, $refresh_token;

    public function __construct( array $config = [] )
    {
        $this->config = array_merge([
            'host' => self::API_BASE_PATH,
            'partner_id' => '',
            'partner_key' => '',
            'code' => '',
            'shop_id' => '',
            'access_token' => '',
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
    
    function getShopInfo() {
        $host = $this->host;
        $path = "/api/v2/shop/get_shop_info";
        
        $accessToken = $this->access_token;
        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer
        
        $timest = time();
        $tokenBaseString = sprintf("%s%s%s%s%s", $partnerId, $path, $timest, $accessToken, $shopId);
        $sign = hash_hmac('sha256', $tokenBaseString, $partnerKey);
        $api_url = sprintf("%s%s?access_token=%s&partner_id=%s&shop_id=%s&sign=%s&timestamp=%s", $host, $path, $accessToken, $partnerId, $shopId, $sign, $timest );

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
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            $ret = json_decode($response, true);
            $this->err_status = $ret["error"];
            $this->err_message = $ret["message"];

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

    function getProfile() {
        $host = $this->host;
        $path = "/api/v2/shop/get_profile";

        $accessToken = $this->access_token;
        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer
    
        $timest = time();
        $tokenBaseString = sprintf("%s%s%s%s%s", $partnerId, $path, $timest, $accessToken, $shopId);
        $sign = hash_hmac('sha256', $tokenBaseString, $partnerKey);
        $api_url = sprintf("%s%s?access_token=%s&partner_id=%s&shop_id=%s&sign=%s&timestamp=%s", $host, $path, $accessToken, $partnerId, $shopId, $sign, $timest );
    
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
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json'
                ),
            ));
    
            $response = curl_exec($curl);
            $ret = json_decode($response, true);
            $this->err_status = $ret["error"];
            $this->err_message = $ret["message"];
    
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