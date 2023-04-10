<?php

namespace Drupal\social_api\Plugin\SocialApi;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\social_api\SocialApiBase;
use Drupal\social_api\SocialApiInterface;
use Drupal\social_api\CurlBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Psr\Container\ContainerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * @SocialApi(
 *     id = "social_api_facebook",
 *     admin_label = @Translation("Facebook"),
 *     base_url = "https://graph.facebook.com/v15.0"
 * )
 */
class FacebookApi extends SocialApiBase implements SocialApiInterface, ContainerFactoryPluginInterface{

    protected $time;

    protected $dateFormatter;

    protected $currentUser;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time, DateFormatterInterface $dateFormatter, AccountProxyInterface $currentUser) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->time = $time;
        $this->dateFormatter = $dateFormatter;
        $this->currentUser = $currentUser;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('datetime.time'),
            $container->get('date.formatter'),
            $container->get('current_user')
        );
    }

    public function setFormFields() {
        $form = parent::setFormFields();

        $form['app_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('App ID')
        ];
        $form['app_secret'] = [
            '#type' => 'textfield',
            '#title' => $this->t('App Secret')
        ];

        //Check if saved access token is expired or not
        if(!empty($this->api_config['access_token'])){
            $debug_params = ['input_token' => $this->api_config['access_token']];
            $token_data = $this->makeRequest('/debug_token', $debug_params);
            $exp_date = $token_data['expires_at'];
            $is_expired = ($exp_date < $this->time->getRequestTime());
            if($is_expired){
                $form['login_link'] = $this->getFacebookLoginLink();
            }else{
                $form['access_token_expiration'] = [
                    '#markup' => '<p>Token expires on: '.$this->dateFormatter->format($exp_date).'</p>'
                ];
            }
        }else{
            $form['login_link'] = $this->getFacebookLoginLink();
        }

        return $form;
    }

    public function makeContentRenderable() {

        if(!empty($this->api_config['access_token'])) {
            $posts = $this->makeRequest('/me/posts', ['fields' => 'full_picture,message,created_time,permalink_url,to']);
            $user = $this->api_config['user'];
            $user['timezone'] = $this->currentUser->getTimeZone();
            ksm('Facebook API Called');
            return[
                '#theme' => 'facebook_posts',
                '#user' => $this->api_config['user'],
                '#posts' => $this->processPosts($posts),
            ];
        }else{
            return [
                '#markup' => $this->t('Facebook API: Missing access token')
            ];
        }
    }

    /**
     * A helper method to keep setFormFields clean
     */
    private function getFacebookLoginLink(){
        if(!empty($this->api_config['app_id'])) {
            $params = [
                'client_id' => $this->api_config['app_id'],
                'redirect_uri' => 'https://d9-sandbox.docksal/social-api/facebook/login',
                'state' => $this->getPluginDefinition()['id']
            ];

            //Generate FB login link
            $curl = new CurlBuilder('https://www.facebook.com/v15.0', '/dialog/oauth', $params);
            $login_url = Url::fromUri($curl->getRequestUrl());
            $login_link = Link::fromTextAndUrl('Login to Facebook', $login_url);

            return $login_link->toRenderable();
        } else{
            return ['#markup' => $this->t('Set your App ID and secret in order to login')];
        }

    }


    /**
     * Helper method to simplify Facebook calls and handling error codes
     */
    private function makeRequest(string $url, array $params = []) {
        $params['access_token'] =  $this->api_config['access_token'];

        $curl = new CurlBuilder($this->getBaseUrl(), $url, $params);
        $response = $curl->executeCurl();
        if(array_key_exists('error', $response)){
            throw new \Exception('Graph API error: '.$response['error']['message']);
        }else if(array_key_exists('data', $response)){
            return $response['data'];
        }else{
            return $response;
        }
    }

    private function processPosts(array $posts) {
        foreach ($posts as &$post) {
            if (isset($post['to'])) {
                foreach ($post['to']['data'] as $to) {
                    $url = Url::fromUri('https://facebook.com/'.$to['id']);
                    $link = Link::fromTextAndUrl($to['name'], $url);
                    $post['message'] = str_replace($to['name'], $link->toString(), $post['message']);
                }
            }
        }
        return $posts;
    }

}