<?php

namespace Drupal\social_api\Plugin\SocialApi;

use Drupal\social_api\SocialApiBase;
use Drupal\social_api\SocialApiInterface;
use Drupal\social_api\CurlBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * @SocialApi(
 *     id = "social_api_linkedin",
 *     admin_label = @Translation("LinkedIn"),
 *     base_url = "https://api.linkedin.com/v2"
 * )
 */
class LinkedinApi extends SocialApiBase implements SocialApiInterface{

    public function setFormFields() {
        $form = parent::setFormFields();

        $form['client_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Client ID')
        ];
        $form['client_secret'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Client Secret')
        ];

        if(!empty($this->api_config['client_id']) && !empty($this->api_config['client_secret'])) {
            $form['login_link'] = $this->generateLoginLink();
        }

        return $form;
    }

    public function makeContentRenderable() {

        try{
            $params = [
                'q' => 'owners',
                'owners' => 'urn:li:person:4cag5-F9Pz'
            ];
            ksm($this->makeRequest('/shares', $params));
            //ksm($this->makeRequest('/me'));
        } catch(\Exception $e) {
            \Drupal::messenger()->addError($e->getMessage());
        }

        return[
            '#markup' => 'LinkedIn API'
        ];
    }

    public function generateLoginLink() {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->api_config['client_id'],
            'redirect_uri' => 'https://d9-sandbox.docksal/social-api/linkedin/login',
            'state' => 'linkedin_state_variable',
            'scope' => 'r_liteprofile%20r_emailaddress%20r_member_social%20r_organization_social'
        ];
        $curl = new CurlBuilder('https://www.linkedin.com/oauth/v2', '/authorization', $params);
        $login_url = Url::fromUri($curl->getRequestUrl());
        return Link::fromTextAndUrl('Login to LinkedIn', $login_url)->toRenderable();
    }

    public function makeRequest(string $url, array $params = [], array $options = []) {
        $curl = new CurlBuilder($this->getBaseUrl(), $url, $params);
        $options[] = 'Authorization: Bearer '.$this->api_config['access_token'];
        $curl->setOption(CURLOPT_HTTPHEADER, $options);
        $response = $curl->executeCurl();
        if(array_key_exists('serviceErrorCode', $response)) {
            throw new \Exception('LinkedIn API Error: ('.$response['status'].') '.$response['message']);
        }else{
            return $response;
        }
    }

}