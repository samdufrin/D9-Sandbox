<?php

namespace Drupal\social_api;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class IcecreamManager extends DefaultPluginManager{

  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  )
  {
    parent::__construct(
      'Plugin/Flavor',
      $namespaces,
      $module_handler,
      '\Drupal\social_api\FlavorInterface',
      'Drupal\social_api\Annotation\Flavor'
    );
    $this->alterInfo('icecream_flavors_info');
    $this->setCacheBackend($cache_backend, 'icecream_flavors');
  }

}
