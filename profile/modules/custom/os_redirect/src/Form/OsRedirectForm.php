<?php

namespace Drupal\os_redirect\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\redirect\Form\RedirectForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OsRedirectForm.
 *
 * @package Drupal\os_redirect\Form
 */
class OsRedirectForm extends RedirectForm {

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, ModuleHandlerInterface $moduleHandler, EntityTypeManagerInterface $entity_type_manager) {
    $this->setEntity($entity_type_manager->getStorage('redirect')->create());
    $this->setModuleHandler($moduleHandler);
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('module_handler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['status_code']['#access'] = FALSE;
    $form['language']['widget']['#access'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContext */
    $vsiteContext = \Drupal::service('vsite.context_manager');
    if ($purl = $vsiteContext->getActivePurl()) {
      $source = $form_state->getValue(['redirect_source', 0]);
      $form_state->setValue('redirect_source', [['path' => $purl . '/' . $source['path']]]);
    }
    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirect('os_redirect.list');
  }

}
