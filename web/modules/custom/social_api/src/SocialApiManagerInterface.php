<?php

namespace Drupal\social_api;

interface SocialApiManagerInterface{

    public function generateFormFields(&$form, array $configuration);

}