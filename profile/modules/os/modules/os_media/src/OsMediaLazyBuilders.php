<?php

namespace Drupal\os_media;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles lazy builder of media entities.
 */
class OsMediaLazyBuilders implements ContainerInjectionInterface {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
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

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Renders the media entity to the given width.
   *
   * @param int $id
   *   The id of the media entity to render.
   * @param string $width
   *   The width of the final media entity render, or 'default' if no width is set.
   */
  public function renderMedia($id, $width) {
    if ($entity = $this->entityTypeManager->getStorage('media')->load($id)) {
      if ($width != 'default') {
        $entity->dimensions['width'] = $width;
      }
      $output = $this->entityTypeManager->getViewBuilder('media')->view($entity);
      return $output;
    }

    // No media entity of $id found.
    // Fallback to empty string.
    // TODO: Display warning for privledged users?
    return [
      '#type' => 'markup',
      '#markup' => "",
    ];
  }

}
