<?php

namespace Drupal\social_api;

class CurlBuilder{

    private $curl;

    public function __construct(string $base_url, string $url, array $params = []) {
        $query_parameters = (empty($params)) ? '' : '?';
        foreach($params as $key => $param){
            $query_parameters .= $key.'='.$param;
            if(array_key_last($params) !== $key){
                $query_parameters .= '&';
            }
        }
        $this->curl = curl_init($base_url.$url.$query_parameters);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    /**
     * Set an additional curl option
     */
    public function setOption(string $opt_type, $opt_value){
        curl_setopt($this->curl, $opt_type, $opt_value);
        return $this;
    }

    /**
     * Returns the curl session's URL in case it's needed externally
     */
    public function getRequestUrl(){
        return curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Makes the request and closes the curl session
     */
    public function executeCurl(){
        try{
            $output = curl_exec($this->curl);
        } catch(\Exception $e){
            echo $e->getMessage();
        }
        curl_close($this->curl);
        $this->curl = null;
        return json_decode($output, true);
    }

}