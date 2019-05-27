<?php

namespace Drupal\vsite_favicon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Element;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Vsite Favicon Controller.
 */
class VsiteFaviconController extends ControllerBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager')
    );
  }

  /**
   * VsiteFaviconController constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager
   *   Vsite Context Manager.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager) {
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * New page for edit only favicon on a group.
   */
  public function editFavicon() {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $group_form = $this->entityTypeManager()
      ->getFormObject('group', 'favicon_edit')
      ->setEntity($group);

    $form = $this->formBuilder()->getForm($group_form);
    foreach (Element::children($form['actions']) as $key) {
      // Hide all action buttons, keep only submit.
      if ($key != 'submit') {
        $form['actions'][$key]['#access'] = FALSE;
      }
    }
    return $form;
  }

}
