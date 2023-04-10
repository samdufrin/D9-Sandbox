<?php

namespace Drupal\social_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_api\TwitterRequestBuilder;

/**
 * @Block(
 *   id = "social_api_block",
 *   admin_label = @Translation("Social API Block"),
 * )
 */
class TwitterBlock extends BlockBase{

  private $twitterRequestBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->twitterRequestBuilder = new TwitterRequestBuilder();
  }

  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['#tree'] = true;
    $form['twitter'] = [
      '#type' => 'details',
      '#title' => $this->t('Twitter'),
    ];
    $form['twitter']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->configuration['twitter']['enabled'] ?? 0
    ];
    $form['twitter']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Username'),
      '#default_value' => $this->configuration['twitter']['username'] ?? '',
    ];
    $form['twitter']['bearer_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer Token'),
      '#default_value' => $this->configuration['twitter']['bearer_token'] ?? ''
    ];
    $form['twitter']['max_results'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of tweets'),
      '#default_value' => $this->configuration['twitter']['max_results'] ?? 1
    ];
    if(!empty($this->configuration['twitter_user_id'])){
      $form['twitter']['twitter_username']['#suffix'] = $this->t('<p>User ID: '.$this->configuration['twitter_user_id'].'</p>');
    }

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $twitter_user_id = $this->twitterRequestBuilder->getTwitterUser($form_state->getValue(['twitter', 'username']))['id'];
    $this->configuration['twitter'] = [
      'bearer_token' => $form_state->getValue(['twitter', 'bearer_token']),
      'username' => $form_state->getValue(['twitter', 'username']),
      'user_id' => $twitter_user_id,
      'enabled' => $form_state->getValue(['twitter', 'enabled']),
      'max_results' => $form_state->getValue(['twitter', 'max_results']),
    ];
  }

  public function build()
  {
    $render = [];

    if($this->configuration['twitter']['enabled'] == 1){
      $user_id = $this->configuration['twitter']['user_id'];
      $max_results = $this->configuration['twitter']['max_results'];
      $tweets = $this->twitterRequestBuilder->makeRequest('/users/'.$user_id.'/tweets');
      $rows = [];
      foreach($tweets['data'] as $i => $tweet){
        if($i < $max_results){
          $rows[] = [$tweet['text']];
        }
      }
      $render['twitter'] = [
        '#type' => 'table',
        '#title' => $this->t('Elon tweets'),
        '#header' => ['Tweets'],
        '#rows' => $rows
      ];
    }

    return $render;
  }

}
