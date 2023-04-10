<?php

namespace Drupal\social_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\social_api\CurlBuilder;

class FacebookLogin extends ControllerBase{

    protected $configFactory;

    protected $messenger;

    private $api_config;

    public function __construct(ConfigFactoryInterface $configFactory, MessengerInterface $messenger){
        $this->configFactory = $configFactory;
        $this->messenger = $messenger;
        $this->api_config = $this->configFactory->getEditable('social_api.settings');
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('config.factory'),
            $container->get('messenger')
        );
    }


    public function login() {
        $base_url = 'https://graph.facebook.com/v15.0';
        $api_config_values = $this->api_config->getRawData()['social_api_facebook'];
        if(array_key_exists('code', $_GET)) {
            try {
                $params = [
                    'client_id' => $api_config_values['app_id'],
                    'redirect_uri' => 'https://d9-sandbox.docksal/social-api/facebook/login',
                    'client_secret' => $api_config_values['app_secret'],
                    'code' => $_GET['code'],
                ];
                $curl = new CurlBuilder($base_url, '/oauth/access_token', $params);
                $response = $curl->executeCurl();
            }catch (\Exception $e) {
                $this->messenger->addError($e->getMessage());
            }

            try {
                $user_params = ['fields' => 'name,picture', 'access_token' => $response['access_token']];
                $user_curl = new CurlBuilder($base_url, '/me', $user_params);
                $user_response = $user_curl->executeCurl();
            } catch (\Exception $e) {
                $this->messenger->addError($e->getMessage());
            }
            $this->api_config
                ->set('social_api_facebook.access_token', $response['access_token'])
                ->set('social_api_facebook.user.name', $user_response['name'])
                ->set('social_api_facebook.user.picture', $user_response['picture']['data']['url'])
                ->save();

            drupal_flush_all_caches();
        }

        $home_url = Url::fromRoute('<front>');
        $redirect = new RedirectResponse($home_url->toString());
        $redirect->send();
        return [];
    }

}