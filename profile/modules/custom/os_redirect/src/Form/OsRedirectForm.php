<?php

namespace Drupal\os_redirect\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\redirect\Entity\Redirect;
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
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, ModuleHandlerInterface $moduleHandler) {
    $this->setEntity(new Redirect([], 'redirect'));
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
      $container->get('module_handler')
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

}
