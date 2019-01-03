<?php

namespace Drupal\vsite_privacy\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VsitePrivacyForm.
 */
class VsitePrivacyForm extends ConfigFormBase {

  /**
   * Vsite privacy level manager.
   *
   * @var \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface
   */
  protected $vsitePrivacyLevelManager;

  /**
   * Creates new VsitePrivacyForm object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, VsitePrivacyLevelManagerInterface $vsitePrivacyLevelManager) {
    parent::__construct($config_factory);
    $this->vsitePrivacyLevelManager = $vsitePrivacyLevelManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('vsite.privacy.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['vsite.privacy'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vsite_privacy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $privacy = $this->configFactory()->get('vsite.privacy');

    $level = $privacy->get('level');

    $form['privacy_level'] = [
      '#title' => t('Privacy Level'),
      '#description' => t('Sets the privacy level for the entire site. Apps can override this when the site is 
        public, but not private.'),
      '#type' => 'radios',
      '#options' => $this->vsitePrivacyLevelManager->getOptions(),
      '#default_value' => $level ? $level : 'public',
    ];

    $descriptions = $this->vsitePrivacyLevelManager->getDescriptions();
    foreach ($descriptions as $elem => $text) {
      $form['privacy_level'][$elem]['#description'] = $text;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $privacy = $this->configFactory()->getEditable('vsite.privacy');

    $privacy->set('level', $form_state->getValue('privacy_level'));
    $privacy->save(TRUE);

    parent::submitForm($form, $form_state);
  }

}
