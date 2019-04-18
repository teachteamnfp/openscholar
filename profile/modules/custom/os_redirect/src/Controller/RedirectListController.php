<?php

namespace Drupal\os_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to list vsite redirects.
 */
class RedirectListController extends ControllerBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  private $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager')
    );
  }

  /**
   * Shows redirect administration page.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing() {
    $redirects = [];
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $redirects = $group->getContentEntities('group_entity:redirect');
    }

    $header = [
      'source' => $this->t('Source'),
      'path' => $this->t('Redirect'),
      'delete' => $this->t('Actions'),
    ];

    $rows = [];
    foreach ($redirects as $redirect) {
      if (empty($redirect)) {
        continue;
      }
      $rows[] = [
        'data' => [
          $redirect->get('redirect_source')->getValue()[0]['path'],
          $redirect->get('redirect_redirect')->getValue()[0]['uri'],
          Link::createFromRoute($this->t('Delete'), 'os_redirect.delete', ['redirect' => $redirect->id()])->toString(),
        ],
      ];
    }
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}
