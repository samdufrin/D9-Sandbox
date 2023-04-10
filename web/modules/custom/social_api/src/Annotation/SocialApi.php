<?php
/**
 * @file
 * Contains \Drupal\social_api\Annotation\SocialApi
 */

namespace Drupal\social_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the base configurable fields for the SocialApi plugin
 *
 * @see \Drupal\social_api\SocialApiManager
 * @see social_api
 *
 * @Annotation
 */
class SocialApi extends Plugin{

    /**
    * The plugin ID
    *
    * @var string
    */
    public $id;

    /**
     * The viewable plugin label
     */
    public $admin_label;

    /**
    * The API base url
    *
    * @var string
    */
    public $base_url;

}
