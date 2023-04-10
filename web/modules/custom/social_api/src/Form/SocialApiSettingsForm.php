<?php

namespace Drupal\social_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_api\SocialApiManagerInterface;
use Psr\Container\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

class SocialApiSettingsForm extends FormBase{

    protected $configFactory;

    protected $socialApiManager;

    protected $messenger;

    private $configuration;

    public function __construct(ConfigFactoryInterface $configFactory, SocialApiManagerInterface $socialApiManager, MessengerInterface $messenger){
        $this->configFactory = $configFactory;
        $this->socialApiManager = $socialApiManager;
        $this->messenger = $messenger;
        $this->configuration = $this->configFactory->getEditable('social_api.settings');
    }

    public static function create( ContainerInterface $container) {
        return new static(
            $container->get('config.factory'),
            $container->get('plugin.manager.social_api'),
            $container->get('messenger')
        );
    }

    public function getFormId() {
        return 'social_api.settings_form';
    }

    public function getEditableConfigNames() {
        return ['social_api.settings'];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = true;
        $form['description'] = [
              '#markup' => '<h5>Configure instances of various social media APIs to retrieve data</h5>'
        ];

        $this->socialApiManager->generateFormFields($form, $this->configuration->getRawData());

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Save Configuration'
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();
        $definitions = array_keys($this->socialApiManager->getDefinitions());
        foreach($definitions as $definition) {
            foreach($values[$definition] as $key => $value) {
                $this->configuration->set($definition.'.'.$key, $values[$definition][$key]);
            }
        }
        $this->configuration->save();

        $this->messenger->addStatus('Settings saved');
    }

}
