<?php

namespace Drupal\social_api;

class TwitterRequestBuilder{

  private const API_BASE_URL = 'https://api.twitter.com/2';

  private $bearer_token;

  public function __construct(){
    $this->bearer_token = \Drupal::config('block.block.socialapiblock')->get('settings.twitter.bearer_token');
  }

  public function makeRequest(string $url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer '.$this->bearer_token,
      'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_URL, self::API_BASE_URL.$url);
    try{
      $output = curl_exec($curl);
      curl_close($curl);
    }catch(\Exception $e){
      throw $e;
    }

    return json_decode($output, true);
  }

  public function getTwitterUser(string $username){
    $user = $this->makeRequest('/users/by?usernames='.$username);
    return $user['data'][0];
  }

}
