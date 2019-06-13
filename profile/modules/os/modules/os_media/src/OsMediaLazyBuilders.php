<?php

namespace Drupal\os_media;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OsMediaLazyBuilders  implements ContainerInjectionInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

    /**
     * {@inheritdoc}
     */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }


  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function renderMedia($id) {
    if ($entity = $this->entityTypeManager->getStorage('media')->load($id)) {
      return $this->entityTypeManager->getViewBuilder('media')->view($entity);
    }

    // No media entity of $id found.
    // Fallback to empty string.
    // TODO: Display warning for privledged users?
    return [
      '#type' => 'markup',
      '#markup' => ""
    ];
  }
}
