<?php

namespace Drupal\social_api;

use Drupal\Core\Plugin\PluginBase;

class FlavorBase extends PluginBase implements FlavorInterface{

  public function getName() {
    return $this->pluginDefinition['name'];
  }

  public function getPrice() {
    return $this->pluginDefinition['price'];
  }

  public function slogan() {
    return t('Best flavor ever');
  }

}
