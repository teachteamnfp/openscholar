<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Base class for os_widgets tests.
 */
class OsWidgetsExistingSiteTestBase extends ExistingSiteBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Handle all widgets with plugin manager.
   *
   * @var \Drupal\os_widgets\OsWidgetsManager
   *   Os Widgets Manager.
   */
  protected $osWidgets;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->osWidgets = $this->container->get('plugin.manager.os_widgets');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Creates a block content.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   The created block content entity.
   */
  protected function createBlockContent(array $values = []) {
    $block_content = $this->entityTypeManager->getStorage('block_content')->create($values + [
      'type' => 'basic',
    ]);
    $block_content->enforceIsNew();
    $block_content->save();

    $this->markEntityForCleanup($block_content);

    return $block_content;
  }

  /**
   * Creates a reference.
   *
   * @param array $values
   *   (Optional) Default values for the reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   The new reference entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createReference(array $values = []) : ReferenceInterface {
    $reference = Reference::create($values + [
      'title' => $this->randomString(),
      'type' => 'artwork',
      'bibcite_year' => [
        'value' => 1980,
      ],
      'distribution' => [
          [
            'value' => 'citation_distribute_repec',
          ],
      ],
      'status' => [
        'value' => 1,
      ],
    ]);

    $reference->save();

    $this->markEntityForCleanup($reference);

    return $reference;
  }

  /**
   * Creates a contributor.
   *
   * @param array $values
   *   (Optional) Default values for the contributor.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface
   *   The new contributor entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContributor(array $values = []) : ContributorInterface {
    $contributor = Contributor::create($values + [
      'first_name' => $this->randomString(),
      'middle_name' => $this->randomString(),
      'last_name' => $this->randomString(),
    ]);

    $contributor->save();

    $this->markEntityForCleanup($contributor);

    return $contributor;
  }

  /**
   * Creates a media.
   *
   * @param array $values
   *   (Optional) Default values for the media.
   *
   * @return \Drupal\media\MediaInterface
   *   The new media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createMedia(array $values = []) : MediaInterface {
    $media = Media::create($values + [
      'bundle' => 'default',
      'name' => $this->randomMachineName(8),
      'status' => 1,
    ]);

    $media->save();

    $this->markEntityForCleanup($media);

    return $media;
  }

}
