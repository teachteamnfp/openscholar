<?php

namespace Drupal\os_widgets\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle the saving of a collection of blocks into LayoutContexts.
 */
class LayoutManagementController extends ControllerBase {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request_stack'));
  }

  /**
   * LayoutManagementController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   *   The request we're checking data for.
   */
  public function __construct(RequestStack $stack) {
    $this->requestStack = $stack;
  }

  /**
   * Save the blocks sent to us to the topmost LayoutContext.
   *
   * Only saves the diff.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The return result
   */
  public function saveLayout() {
    $context_ids = $this->requestStack->getCurrentRequest()->request->get('contexts');
    $blocks = $this->requestStack->getCurrentRequest()->request->get('blocks');

    /** @var \Drupal\os_widgets\Entity\LayoutContext[] $contexts */
    $contexts = $this->entityTypeManager()->getStorage('layout_context')->loadMultiple($context_ids);

    uasort($contexts, ['ConfigEntityBase', 'sort']);
    $target = array_shift($contexts);

    $contexts = array_reverse($contexts);
    $parent_blocks = [];
    foreach ($contexts as $a) {
      $context_blocks = $a->getBlockPlacements();
      $parent_blocks = array_merge($parent_blocks, $context_blocks);
    }

    @uasort($blocks, ['Block', 'sort']);
    $adjusted = $this->adjustRelativeWeights($blocks, $parent_blocks);
    $data = $this->filterBlocks($adjusted, $parent_blocks);

    if ($data) {
      $target->setBlockPlacements($data);
      $target->save();
    }

    $response = new JsonResponse();
    $response->setData([
      'blocks' => $data,
    ]);
    return $response;
  }

  /**
   * Resets the layout back to the default.
   *
   * In practice, this deletes the layout from the vsite storage.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The return result.
   */
  public function resetLayout() {
    $response = new JsonResponse();
    try {
      $context_ids = $this->requestStack->getCurrentRequest()->request->get('contexts');
      /** @var \Drupal\os_widgets\LayoutContextInterface[] $contexts */
      $contexts = $this->entityTypeManager()->getStorage('layout_context')->loadMultiple($context_ids);

      uasort($contexts, ['ConfigEntityBase', 'sort']);
      $target = array_shift($contexts);
      $target->delete();
    }
    catch (\Exception $e) {
      $response->setStatusCode(500);
      $response->setData(['error_message' => $e->getMessage()]);
    }

    return $response;
  }

  /**
   * Adjust the weights of blocks such that parent blocks can be left unchanged.
   *
   * @param array $adjustee
   *   These blocks will be adjusted to fit into the parent.
   * @param array $parent
   *   The parent blocks, which $context will be adjusted to fit in.
   *
   * @return array
   *   The blocks adjusted to fit around the parent.
   */
  protected function adjustRelativeWeights(array $adjustee, array $parent) {
    $adjustee_regions = $this->splitByRegion($adjustee);
    $parent_regions = $this->splitByRegion($parent);

    foreach ($adjustee_regions as $r => &$blocks) {
      // Determine if the parent blocks are in the same order in
      // the parent and new.
      $new_keys = array_keys($blocks);
      // Ensure parent_regions has the region.
      if (!isset($parent_regions[$r])) {
        $parent_regions[$r] = [];
      }

      $parent_keys = array_keys($parent_regions[$r]);
      // The parent keys that exist in the child
      // call array values because array_intersect preserves keys.
      $only_parent_keys = array_values(array_intersect($new_keys, $parent_keys));
      // The order in the parent of keys that only exist in child.
      $active_parents = array_values(array_intersect($parent_keys, $only_parent_keys));

      $bulldoze = TRUE;
      if (empty($only_parent_keys)) {
        // None of the parent blocks are in the child
        // bulldoze is fine.
      }
      elseif ($only_parent_keys === $active_parents) {
        // Parent blocks are in the same order.
        // update their weights to match the parents
        // then put the new blocks in between them.
        $bulldoze = FALSE;
      }

      // We can't just bulldoze the weights.
      if (!$bulldoze) {
        $min_weight = INF;
        $between = [];
        foreach ($blocks as $bid => &$b) {
          if (in_array($bid, $parent_keys)) {
            // Set the weights of blocks in the parent
            // these are fixed in stone as far as we're concerned.
            $b['weight'] = $parent_regions[$r][$bid]['weight'];
            $adjustee[$bid]['weight'] = $b['weight'];
            // Go back through the children ahead of
            // this one and set their weights.
            $count = count($between);
            if ($count) {
              // Handle case where children are before any parent.
              if ($min_weight === INF) {
                $min_weight = $b['weight'] - $count - 1;
              }
              // Get the range we have to work with.
              $range = $b['weight'] - $min_weight;
              // Get the difference between children weights
              // based on the number of children.
              $delta = $range / ($count + 1);
              // Set the weights for the children.
              foreach ($between as $pos => $b_id) {
                $adjustee[$b_id]['weight'] = $min_weight + ($pos + 1) * $delta;
              }
            }

            $min_weight = $b['weight'];
            $between = [];
          }
          else {
            // Put together a list of all children.
            $between[] = $bid;
          }
        }
        // Handle case where children are after all parents.
        foreach ($between as $pos => $b_id) {
          $adjustee[$b_id]['weight'] = $min_weight + $pos + 1;
        }
      } // End no bulldoze processing
      // if its ok to bulldoze, we can use the integer
      // values that are already in place.
      // nothing needs to be done.
    }

    return $adjustee;
  }

  /**
   * Filter a block set so it only contains a diff from another set of blocks.
   *
   * @param array $blocks
   *   The set of blocks to check against.
   * @param array $parent_blocks
   *   The blocks in parent contexts.
   *
   * @return array
   *   List of blocks without any that match a parent.
   */
  protected function filterBlocks(array $blocks, array $parent_blocks) {
    // Fields to check for changes.
    $block_fields = [
      'region',
      'status',
      'title',
      'weight',
    ];

    $child_blocks = [];
    foreach ($blocks as $bid => $b) {
      // If the block exists in a parent, check for overrides.
      if (isset($parent_blocks[$bid])) {
        $changed = FALSE;
        foreach ($block_fields as $field) {
          // One of the block's fields has been changed.
          if ($parent_blocks[$bid][$field] != $blocks[$bid][$field]) {
            $changed = TRUE;
          }
        }
        // If something changed, it needs to be saved in the child context.
        if ($changed) {
          $child_blocks[$bid] = $b;
        }
      }
      // If the block doesn't exist in the parent, but is still in a region.
      elseif ($b['region']) {
        // dpm($bid.': weight: new: '.$blocks[$bid]['weight']);.
        $child_blocks[$bid] = $b;
      }
    }

    return $child_blocks;
  }

  /**
   * Returns an array of arrays. First key is region, second key is block id.
   *
   * @param array $blocks
   *   Flat list of blocks.
   *
   * @return array
   *   Blocks split into regions and sorted by weight.
   */
  protected function splitByRegion(array $blocks) {
    $regions = [];
    foreach ($blocks as $bid => $b) {
      if ($b['region']) {
        $regions[$b['region']][$bid] = $b;
      }
    }

    foreach ($regions as $r => $bs) {
      uasort($regions[$r], [$this, 'blockSort']);
    }

    return $regions;
  }

  /**
   * Sort blocks by weight.
   */
  private function blockSort($a, $b) {
    $aw = is_object($a) ? $a->weight : $a['weight'];
    $bw = is_object($b) ? $b->weight : $b['weight'];
    return ($aw - $bw);
  }

}
