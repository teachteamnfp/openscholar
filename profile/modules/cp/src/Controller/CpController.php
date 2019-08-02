<?php

namespace Drupal\cp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cp\CpManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates responses for CP settings routes.
 *
 * Inspired from SystemController.
 *
 * @see \Drupal\system\Controller\SystemController
 */
class CpController extends ControllerBase {

  /**
   * Cp manager service.
   *
   * @var \Drupal\cp\CpManagerInterface
   */
  protected $cpManager;

  /**
   * Creates a new CpController object.
   *
   * @param \Drupal\cp\CpManagerInterface $cp_manager
   *   Cp manager service.
   */
  public function __construct(CpManagerInterface $cp_manager) {
    $this->cpManager = $cp_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cp.cp_manager')
    );
  }

  /**
   * Appearance settings overview page response.
   *
   * @return array
   *   A render array suitable for
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function overview($menu_name): array {
    return $this->cpManager->getBlockContents($menu_name);
  }

}
