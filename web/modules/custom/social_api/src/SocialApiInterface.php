<?php

namespace Drupal\social_api;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface SocialApiInterface extends PluginInspectionInterface{

    /**
    * Generate the necessary fields for an API form
    *
    * @return array
    */
    public function setFormFields();

    /**
    * Render retrieved content from an API
    *
    * @return array
    */
    public function makeContentRenderable();

}
