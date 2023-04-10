<?php

namespace Drupal\social_api\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_api\CurlBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedinLogin extends ControllerBase{

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
        if(array_key_exists('code', $_GET)) {
            $api_config_values = $this->api_config->getRawData()['social_api_linkedin'];

            try {
                $params = [
                    'grant_type' => 'authorization_code',
                    'code' => $_GET['code'],
                    'redirect_uri' => 'https://d9-sandbox.docksal/social-api/linkedin/login',
                    'client_id' => $api_config_values['client_id'],
                    'client_secret' => $api_config_values['client_secret']
                ];
                $curl = new CurlBuilder('https://www.linkedin.com', '/oauth/v2/accessToken', $params);
                $curl
                    ->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded'])
                    ->setOption(CURLOPT_POST, 1);
                $response = $curl->executeCurl();
            } catch( \Exception $e) {
                $this->messenger->addError($e->getMessage());
            }

            $this->api_config
                ->set('social_api_linkedin.access_token', $response['access_token'])
                ->set('social_api_linkedin.token_expires', $response['expires_in'])
                ->save();
        }

        $redirect = new RedirectResponse('/');
        $redirect->send();

        return [];
    }

}