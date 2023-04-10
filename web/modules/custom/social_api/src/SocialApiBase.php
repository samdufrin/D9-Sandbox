<?php

namespace Drupal\social_api;

use Drupal\Core\Plugin\PluginBase;

class SocialApiBase extends PluginBase {

    /**
     * A specific block's configuration settings which is set when the plugin is loaded.
     * This allows multiple blocks to exist that utilize different configuration
     */
    public $api_config;

    /**
     * Includes form fields that should be consistent across all plugins
     *
     * @return array
     */
    public function setFormFields() {
        $form['display_api'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Display API'),
        ];
        $form['api_timeout'] = [
            '#type' => 'number',
            '#title' => $this->t('API Timeout'),
            '#description' => $this->t('The number of seconds until the API will refresh with new content')
        ];

        return $form;
    }


    public function setApiConfiguration(array $configuration) {
        if(!empty($configuration[$this->getPluginDefinition()['id']])) {
            $this->api_config = $configuration[$this->getPluginDefinition()['id']];
        }
    }


    /**
     * Returns a plugin's API base URL as defined in annotation
     *
     * @return string
     */
    public function getBaseUrl(){
        return $this->pluginDefinition['base_url'];
    }

}
