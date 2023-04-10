<?php

namespace Drupal\social_api;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * SocialApi plugin manager
 */
class SocialApiManager extends DefaultPluginManager implements SocialApiManagerInterface{

    /**
    * Constructs a SocialApi object
    */
    public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
    ){
        parent::__construct(
          'Plugin/SocialApi',
          $namespaces,
          $module_handler,
          'Drupal\social_api\SocialApiInterface',
          'Drupal\social_api\Annotation\SocialApi'
        );
        $this->alterInfo('social_apis_info');
        $this->setCacheBackend($cache_backend, 'social_apis');
    }

    /**
     * Apply all plugin form definitions to a given form array
     */
    public function generateFormFields(&$form, array $configuration){
        //Retrieve plugin definitions
        $definitions = $this->getDefinitions();
        foreach($definitions as $id => $definition){
            //Create an instance of each plugin and generate that plugin's form fields
            $form[$id] = [
                '#type' => 'details',
                '#title' => $definition['admin_label'],
            ];
            $api = $this->createInstance($id);
            $api->setApiConfiguration($configuration);
            foreach($api->setFormFields($form) as $field_id => $field){
                if(!array_key_exists('#type', $field)){
                    $form[$id][$field_id] = $field;
                    break;
                }
                if($field['#type'] === 'textfield' || $field['#type'] === 'checkbox' || $field['#type'] === 'number'){
                    $field['#default_value'] = $configuration[$id][$field_id] ?? '';
                }
                $form[$id][$field_id] = $field;
            }
        }
    }
}
