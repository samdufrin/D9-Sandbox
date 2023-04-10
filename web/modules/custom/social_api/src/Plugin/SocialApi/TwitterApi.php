<?php

namespace Drupal\social_api\Plugin\SocialApi;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\social_api\SocialApiBase;
use Drupal\social_api\SocialApiInterface;
use Drupal\social_api\CurlBuilder;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Psr\Container\ContainerInterface;

/**
 * @SocialApi(
 *     id = "social_api_twitter",
 *     admin_label = @Translation("Twitter"),
 *     base_url = "https://api.twitter.com/2"
 * )
 */
class TwitterApi extends SocialApiBase implements SocialApiInterface, ContainerFactoryPluginInterface{

    /**
     * @var MessengerInterface
     */
    protected $messenger;

    /**
     * @var AccountProxyInterface
     */
    protected $currentUser;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, AccountProxyInterface $currentUser) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->messenger = $messenger;
        $this->currentUser = $currentUser;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('messenger'),
            $container->get('current_user')
        );
    }

    /**
     * Any form fields specific to this API should be included in this method (API authorization data).
     * This method should ALWAYS be called from the parent
     *
     * @return array
     */
    public function setFormFields() {
        $form = parent::setFormFields();
        $form['target_account'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Target Account ID'),
            '#description' => $this->t('The numeric ID of the desired Twitter account')
        ];
        $form['bearer_token'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Bearer Token')
        ];

        return $form;
    }

    /**
     * This method determines how your plugin's retrieved content will be rendered wherever it's instantiated
     *
     * @return array
     */
    public function makeContentRenderable() {
        $twitter_id = $this->api_config['target_account'];
        try{
            $params = ['ids' => $twitter_id, 'user.fields' => 'profile_image_url'];
            $user = $this->makeRequest('/users', $params)['data'][0];
            $tweet_params = ['tweet.fields' => 'text,created_at,entities', 'expansions' => 'attachments.media_keys','media.fields' => 'url'];
            $tweets = $this->makeRequest('/users/'.$twitter_id.'/tweets', $tweet_params);
        } catch(\Exception $e) {
            $this->messenger->addError($e->getMessage());
        }

        //Set the user's timezone
        $user['timezone'] = $this->currentUser->getTimeZone();
        ksm('Twitter API Called');

        return [
            '#theme' => 'twitter_tweets',
            '#tweets' => $this->processTweets($tweets),
            '#user' => $user,
        ];
    }

    /**
     * Twitter-specific wrapper method for executeCurl to define error handling
     */
    public function makeRequest(string $url, array $params = []) {
        $bearer_token = $this->api_config['bearer_token'];
        $curl = new CurlBuilder($this->getBaseUrl(), $url, $params);
        $curl->setOption(CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$bearer_token]);
        $curl = $curl->executeCurl();
        if(array_key_exists('data', $curl)) {
            return $curl;
        }else {
            throw new \Exception('Twitter API Error: No data returned');
        }
    }

    /**
     * Helper method to keep build method clean
     */
    public function processTweets(array $tweets) {
        //Push image URLs into data array
        $image_urls = [];
        if (isset($tweets['includes'])) {
            foreach ($tweets['includes']['media'] as $media) {
                $image_urls[$media['media_key']] = $media['url'];
            }
        }
        foreach ($tweets['data'] as &$tweet) {
            //Replace links with rendered links and images
            if (isset($tweet['entities']['urls'])) {
                foreach ($tweet['entities']['urls'] as $url) {
                    if (isset($url['unwound_url'])) {
                        $link_url = Url::fromUri($url['url']);
                        $link = Link::fromTextAndUrl($url['display_url'], $link_url);
                        $tweet['text'] = str_replace($url['url'], $link->toString(), $tweet['text']);
                    } elseif (isset($url['media_key'])) {
                        $image = '<img src="'.$image_urls[$url['media_key']].'">';
                        $tweet['text'] = str_replace($url['url'], $image, $tweet['text']);
                    }
                }
            }
            //Replace mentions and hashtags with the appropriate links
            if (isset($tweet['entities']['mentions'])) {
                foreach ($tweet['entities']['mentions'] as $mention) {
                    $mention_url = Url::fromUri('https://twitter.com/'.$mention['username']);
                    $mention_link = Link::fromTextAndUrl('@'.$mention['username'], $mention_url);
                    $tweet['text'] = str_replace('@'.$mention['username'], $mention_link->toString(), $tweet['text']);
                }
            }
            if (isset($tweet['entities']['hashtags'])) {
                foreach ($tweet['entities']['hashtags'] as $hashtag) {
                    $hashtag_url = Url::fromUri('https://twitter.com/'.$hashtag['tag']);
                    $hashtag_link = Link::fromTextAndUrl('#'.$hashtag['tag'], $hashtag_url);
                    $tweet['text'] = str_replace('#'.$hashtag['tag'], $hashtag_link->toString(), $tweet['text']);
                }
            }
        }

        return $tweets['data'];
    }

}
