<?php

namespace Drupal\os_widgets;

use Drupal\block\BlockRepositoryInterface;
use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\os_widgets\Entity\LayoutContext;

/**
 * Decorates core's Block Repositry to take Layout Contexts into account.
 */
class OsWidgetsBlockRepository implements BlockRepositoryInterface {

  /**
   * The deocarted BlockReository object.
   *
   * @var \Drupal\block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * The entity type manager storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs a new BlockRepository.
   *
   * @param \Drupal\block\BlockRepositoryInterface $blockRepository
   *   The original block repository being decorated.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  public function __construct(BlockRepositoryInterface $blockRepository, EntityTypeManagerInterface $entity_type_manager, ThemeManagerInterface $theme_manager) {
    $this->blockRepository = $blockRepository;
    $this->entityTypeManager = $entity_type_manager;
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibleBlocksPerRegion(array &$cacheable_metadata = []) {
    $output = [];
    $applicable = LayoutContext::getApplicable();

    $limit = \Drupal::request()->query->get('context');

    $limit_found = !$limit;
    $flat = [];
    // TODO: Replace with mechanism to detect when we're on a block_place page.
    $editing = TRUE;

    // Pull down a list of all the blocks in the site.
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    if ($editing) {
      $vsiteContextManager = \Drupal::service('vsite.context_manager');
      if ($vsite = $vsiteContextManager->getActiveVsite()) {
        $blockGCEs = $vsite->getContent('group_entity:block_content');
        foreach ($blockGCEs as $bgce) {
          /** @var \Drupal\block\Entity\BlockContentInterface $block_content */
          $block_content = $bgce->getEntity();
          $instances = $block_content->getInstances();
          if (!$instances) {
            $plugin_id = 'block_content:' . $block_content->uuid();
            $block_id = 'block_content|' . $block_content->uuid();
            $block = $this->entityTypeManager->getStorage('block')->create(['plugin' => $plugin_id, 'id' => $block_id]);
            $block->save();
          }
          else {
            $block = reset($instances);
          }
          $flat[$block->id()] = [
            'id' => $block->id(),
            'region' => 0,
            'weight' => 0,
          ];
        }
      }
    }

    // Take any block in the currently active contexts and
    // place it in the correct region.
    /** @var \Drupal\os_widgets\Entity\LayoutContextInterface $a */
    foreach ($applicable as $a) {
      if ($a->id() == $limit) {
        $limit_found = TRUE;
      }
      if ($limit_found) {
        $context_blocks = $a->getBlockPlacements();
        foreach ($context_blocks as $b) {
          $flat[$b['id']] = $b;
        }
      }
    }

    // Split out the flat list by region while loading the real block.
    foreach ($flat as $b) {
      if ($block = Block::load($b['id'])) {
        $output[$b['region']][$b['weight']] = $block;
      }
    }

    @uasort($outputs, ['Block', 'sort']);

    return $output;
  }

}
