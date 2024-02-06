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

class shopeeProduct
{
    const LIBVER = "2.12.6";
    const USER_AGENT_SUFFIX = "shopee-open-api-client-php/";
    const API_BASE_PATH = 'https://partner.shopeemobile.com';

    private $host, $partner_id, $partner_key, $redirect_uri;
    private $code, $shop_id;
    private $err_status, $err_message, $access_token, $refresh_token;
    private $offset, $page_size, $item_status;
    private $item_id_list, $need_complaint_policy, $need_tax_info;

    public function __construct( array $config = [] )
    {
        $this->config = array_merge([
            'host' => self::API_BASE_PATH,
            'partner_id' => '',
            'partner_key' => '',
            'code' => '',
            'shop_id' => '',
            'access_token' => '',
            'refresh_token' => '',
            'offset' => '0',
            'page_size' => '10',
            'item_status' => 'NORMAL',
            'item_id_list' => '',
            'need_complaint_policy' => true,
            'need_tax_info' => true
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

        if (!is_null($this->config['offset'])) {
            $this->setOffset($this->config['offset']);
            unset($this->config['offset']);
        };

        if (!is_null($this->config['page_size'])) {
            $this->setPageSize($this->config['page_size']);
            unset($this->config['page_size']);
        };

        if (!is_null($this->config['item_status'])) {
            $this->setItemStatus($this->config['item_status']);
            unset($this->config['item_status']);
        };

        if (!is_null($this->config['item_id_list'])) {
            $this->setItemIdList($this->config['item_id_list']);
            unset($this->config['item_id_list']);
        };

        if (!is_null($this->config['need_complaint_policy'])) {
            $this->setNeedComplaintPolicy($this->config['need_complaint_policy']);
            unset($this->config['need_complaint_policy']);
        };

        if (!is_null($this->config['need_tax_info'])) {
            $this->setNeedTaxInfo($this->config['need_tax_info']);
            unset($this->config['need_tax_info']);
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

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this->offset;
    }

    public function setPageSize($page_size) {
        $this->page_size = $page_size;
        return $this->page_size;
    }

    public function setItemStatus($item_status) {
        $this->item_status = $item_status;
        return $this->item_status;
    }

    public function setItemIdList($item_id_list) {
        $this->item_id_list = $item_id_list;
        return $this->item_id_list;
    }

    public function setNeedComplaintPolicy($need_complaint_policy) {
        $this->need_complaint_policy = $need_complaint_policy;
        return $this->need_complaint_policy;
    }

    public function setNeedTaxInfo($need_tax_info) {
        $this->need_tax_info = $need_tax_info;
        return $this->need_tax_info;
    }
    
    public function getItemList() {
        $host = $this->host;
        $path = "/api/v2/product/get_item_list";
        
        $offset = $this->offset;
        $page_size = (int) $this->page_size;
        $item_status = $this->item_status;
        $accessToken = $this->access_token;
        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer        

        $timest = time();
        $tokenBaseString = sprintf("%s%s%s%s%s", $partnerId, $path, $timest, $accessToken, $shopId);
        $sign = hash_hmac('sha256', $tokenBaseString, $partnerKey);
        $api_url = sprintf("%s%s?offset=%s&page_size=%s&item_status=%s&access_token=%s&partner_id=%s&shop_id=%s&sign=%s&timestamp=%s", $host, $path, $offset, $page_size, $item_status , $accessToken, $partnerId, $shopId, $sign, $timest );
    
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
           
    public function getItemBaseInfo() {
        $host = $this->host;
        $path = "/api/v2/product/get_item_base_info";

        $itemIDList = $this->item_id_list;
        $needComplaintPolicy = $this->need_complaint_policy;
        $needTaxInfo = $this->need_tax_info;
        $accessToken = $this->access_token;
        $partnerId = $this->partner_id;
        $partnerKey = $this->partner_key;
        $shopId = (int) $this->shop_id;   // Shop ID must in integer        
    
        $timest = time();
        $tokenBaseString = sprintf("%s%s%s%s%s", $partnerId, $path, $timest, $accessToken, $shopId);
        $sign = hash_hmac('sha256', $tokenBaseString, $partnerKey);
        $api_url = sprintf("%s%s?item_id_list=%s&need_complaint_policy=%s&need_tax_info=%s&access_token=%s&partner_id=%s&shop_id=%s&sign=%s&timestamp=%s", $host, $path, $itemIDList, $needComplaintPolicy, $needTaxInfo , $accessToken, $partnerId, $shopId, $sign, $timest);
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