<?php

namespace Drupal\os_widgets\Controller;


use Drupal\Core\Config\Entity\ConfigEntityBase;
use Symfony\Component\HttpFoundation\Request;

class LayoutManagementController {

  function saveLayout() {
    $context_ids = \Drupal::request()->request->get('contexts');
    $blocks = \Drupal::request()->request->get('blocks');

    /** @var \Drupal\os_widgets\Entity\LayoutContext[] $contexts */
    $contexts = \Drupal::entityTypeManager()->getStorage('layout_context')->loadMultiple($context_ids);

    uasort($contexts, ['ConfigEntityBase', 'sort']);
    $target = array_shift($contexts);

    $contexts = array_reverse($contexts);
    $parent_blocks = [];
    foreach ($contexts as $a) {
      $blocks = $a->getBlockPlacements();
      $parent_blocks = array_merge($parent_blocks, $blocks);
    }

    @uasort($blocks, ['Block', 'sort']);
    $adjusted = $this->adjustRelativeWeights($blocks, $parent_blocks);
    $data = $this->filterBlocks($adjusted, $parent_blocks);
    error_log('data');
    error_log(print_r($data, 1));

    $target->setBlockPlacements($data);
    $target->save();

    return $data;
  }

  /**
   * Adjust the weights of blocks such that parent blocks can be left unchanged.
   *
   * @param $context
   *   These blocks will be adjusted to fit into the parent.
   * @param $parent
   *   The parent blocks, which $context will be adjusted to fit in
   */
  protected function adjustRelativeWeights($adjustee, $parent) {
    $adjustee_regions = $this->splitByRegion($adjustee);
    $parent_regions = $this->splitByRegion($parent);

    foreach ($adjustee_regions as $r => &$blocks) {
      // determine if the parent blocks are in the same order in the parent and new
      $new_keys = array_keys($blocks);
      // ensure parent_regions has the region
      if (!isset($parent_regions[$r])) {
        $parent_regions[$r] = array();
      }

      $parent_keys = array_keys($parent_regions[$r]);
      // the parent keys that exist in the child
      // call array values because array_intersect preserves keys
      $only_parent_keys = array_values(array_intersect($new_keys, $parent_keys));
      // the order in the parent of keys that only exist in child
      $active_parents = array_values(array_intersect($parent_keys, $only_parent_keys));

      $bulldoze = true;
      if (empty($only_parent_keys)) {
        // none of the parent blocks are in the child
        // bulldoze is fine
      }
      elseif ($only_parent_keys === $active_parents) {
        // parent blocks are in the same order.
        // update their weights to match the parents
        // then put the new blocks in between them
        $bulldoze = false;
      }

      // we can't just bulldoze the weights
      if (!$bulldoze) {
        $min_weight = INF;
        $between = array();
        foreach ($blocks as $bid => &$b) {
          if (in_array($bid, $parent_keys)) {
            // set the weights of blocks in the parent
            // these are fixed in stone as far as we're concerned
            $b['weight'] = $parent_regions[$r][$bid]['weight'];
            $adjustee[$bid]['weight'] = $b['weight'];
            // go back through the children ahead of this one and set their weights
            $count = count($between);
            if ($count) {
              // handle case where children are before any parent
              if ($min_weight === 'undefined') {
                $min_weight = $b['weight'] - $count - 1;
              }
              // get the range we have to work with
              $range = $b['weight'] - $min_weight;
              // get the difference between children weights based on the number of children
              $delta = $range/($count + 1);
              // set the weights for the children
              foreach ($between as $pos => $b_id) {
                $adjustee[$b_id]['weight'] = $min_weight + ($pos+1)*$delta;
              }
            }

            $min_weight = $b['weight'];
            $between = array();
          }
          else {
            // put together a list of all children
            $between[] = $bid;
          }
        }
        // handle case where children are after all parents
        foreach ($between as $pos => $b_id) {
          $adjustee[$b_id]['weight'] = $min_weight + $pos + 1;
        }
      } // end no bulldoze processing
      // if its ok to bulldoze, we can use the integer values that are already in place
      // nothing needs to be done
    }
    
    return $adjustee;
  }

  /**
   * Filter a block set so it only contains a diff from another set of blocks.
   *
   * @param array $blocks
   *    The set of blocks to check against.
   * @param $parent_blocks
   * @return array
   */
  protected function filterBlocks($blocks, $parent_blocks) {
    // Fields to check for changes
    $block_fields = array (
      'region',
      'status',
      'title',
      'weight'
    );

    $child_blocks = array ();
    foreach ($blocks as $bid => $b) {
      // if the block exists in a parent, check for overrides
      if (isset($parent_blocks[$bid])) {
        // dpm($bid.': weight: old: '.$parent_blocks[$bid]['weight'].' new: '.$blocks[$bid]['weight']);
        $changed = false;
        foreach ($block_fields as $field) {
          // one of the block's fields has been changed.
          if ($parent_blocks[$bid][$field] != $blocks[$bid][$field]) {
            $changed = true;
          }
        }
        // if something changed, it needs to be saved in the child context
        if ($changed) {
          $child_blocks[$bid] = $b;
        }
      } // if the block doesn't exist in the parent, but is still in a region.
      elseif ($b['region']) {
        // dpm($bid.': weight: new: '.$blocks[$bid]['weight']);
        $child_blocks[$bid] = $b;
      }
    }

    return $child_blocks;
  }

  /**
   * Returns an array of arrays. First key is region, second key is block id.
   *
   * @param $blocks
   */
  protected function splitByRegion($blocks) {
    $regions = array();
    foreach ($blocks as $bid => $b) {
      if ($b['region']) {
        $regions[$b['region']][$bid] = $b;
      }
    }

    foreach ($regions as $r => $bs) {
      uasort($regions[$r], [$this, 'os_layout_block_sort']);
    }

    return $regions;
  }

  private function blockSort($a, $b) {
    $aw = is_object($a) ? $a->weight : $a['weight'];
    $bw = is_object($b) ? $b->weight : $b['weight'];
    return ($aw - $bw);
  }

}
