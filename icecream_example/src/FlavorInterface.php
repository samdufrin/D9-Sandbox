<?php

namespace Drupal\social_api;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface FlavorInterface extends PluginInspectionInterface{

  /**
   * @return string
   */
  public function getName();

  /**
   * @return float
   */
  public function getPrice();

  /**
   * @return string
   */
  public function slogan();

}
