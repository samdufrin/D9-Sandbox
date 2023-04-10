<?php

namespace Drupal\social_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Container\ContainerInterface;
use Drupal\social_api\SocialApiManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * @Block(
 *   id = "social_api_block",
 *   admin_label = @Translation("Social API Block"),
 * )
 */
class SocialApiBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * @var SocialApiManagerInterface $social_api_manager
     */
    protected $socialApiManager;

    protected $configFactory;

    /**
     * @param array $configuration
     * @param $plugin_id
     * @param $plugin_definition
     * @param SocialApiManagerInterface $socialApiManager
     * @param ConfigFactoryInterface $configFactory
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        SocialApiManagerInterface $socialApiManager,
        ConfigFactoryInterface $configFactory
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->socialApiManager= $socialApiManager;
        $this->configFactory = $configFactory;
    }

    /**
     * {@inheritDoc}
     */
    public static function create( ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition){
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('plugin.manager.social_api'),
            $container->get('config.factory')
        );
    }

    /**
     * Renders each plugin's content if configured in the block form
     */
    public function build() {
        $render = [];
        $api_config = $this->configFactory->get('social_api.settings')->getRawData();
        foreach($this->socialApiManager->getDefinitions() as $id => $definition){
            $api = $this->socialApiManager->createInstance($id);
            $api->setApiConfiguration($api_config);
            if(empty($api_config[$id])) {
                break;
            }
            if($api_config[$id]['display_api'] != 0){
                $render[$id] = [
                    $api->makeContentRenderable(),
                    '#cache' => [
                        'max-age' => (empty($api_config[$id]['api_timeout'])) ? 3600 : $api_config[$id]['api_timeout']
                    ]
                ];
            }
        }

        return $render;
    }

}
