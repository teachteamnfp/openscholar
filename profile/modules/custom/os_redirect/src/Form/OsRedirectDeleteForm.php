<?php

namespace Drupal\os_redirect\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\redirect\Form\RedirectDeleteForm;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OsRedirectForm.
 *
 * @package Drupal\os_redirect\Form
 */
class OsRedirectDeleteForm extends RedirectDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, ModuleHandlerInterface $moduleHandler, EntityTypeManagerInterface $entity_type_manager, VsiteContextManagerInterface $vsite_context_manager, RouteMatchInterface $route_match) {
    $redirect_id = $route_match->getParameter('redirect');
    $redirect = $entity_type_manager->getStorage('redirect')->load($redirect_id);
    if (empty($redirect)) {
      throw new NotFoundHttpException($this->t('Redirect entity is not found.'));
    }

    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $vsite_context_manager->getActiveVsite()) {
      $exist_redirect = $group->getContentEntities('group_entity:redirect', ['entity_id' => $redirect_id]);
      if (empty($exist_redirect)) {
        throw new AccessDeniedHttpException();
      }
    }
    $redirect->original = clone $redirect;
    $this->setEntity($redirect);
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
      $container->get('entity_type.manager'),
      $container->get('vsite.context_manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('os_redirect.list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $form_state->setRedirect('os_redirect.list');
  }

}
