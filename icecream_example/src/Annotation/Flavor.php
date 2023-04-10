<?php

namespace Drupal\social_api\Annotation;

use Drupal\Component\Annotation\Plugin;

class Flavor extends Plugin {

  /**
   * @var string
   */
  public $id;

  /**
   * @var \Drupal\Core\Annotation\Translation
   */
  public $name;

  /**
   * @var float
   */
  public $price;

}
