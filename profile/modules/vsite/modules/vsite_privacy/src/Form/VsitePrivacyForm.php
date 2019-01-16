<?php

namespace Drupal\vsite_privacy\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\quickedit\Form\QuickEditFieldForm;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VsitePrivacyForm.
 */
class VsitePrivacyForm extends QuickEditFieldForm {

  /**
   * Vsite privacy level manager.
   *
   * @var \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface
   */
  protected $vsitePrivacyLevelManager;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates new VsitePrivacyForm object.
   */
  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory, ModuleHandlerInterface $moduleHandler, EntityStorageInterface $entityStorage, VsitePrivacyLevelManagerInterface $vsitePrivacyLevelManager, VsiteContextManagerInterface $vsiteContextManager) {
    parent::__construct($privateTempStoreFactory, $moduleHandler, $entityStorage);
    $this->vsitePrivacyLevelManager = $vsitePrivacyLevelManager;
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('module_handler'),
      $container->get('entity.manager')->getStorage('node_type'),
      $container->get('vsite.privacy.manager'),
      $container->get('vsite.context_manager')
    );
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
  public function buildForm(array $form, FormStateInterface $form_state, EntityInterface $entity = NULL, $field_name = NULL) {
    $vsite = $this->vsiteContextManager->getActiveVsite();
    $form = parent::buildForm($form, $form_state, $vsite, 'field_privacy_level');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $form_state->get('entity');
    $entity->save();
  }

}
