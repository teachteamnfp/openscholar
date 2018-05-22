<?php
/**
 * @file
 * os.context.inc
 */

/**
 * Implements hook_context_default_contexts_alter().
 */
function hwpi_context_default_contexts_alter(&$contexts) {
  if (isset($contexts['os_public'])) {
    $fp_blocks = &$contexts['os_public']->reactions['block']['blocks'];
    $fp_blocks['boxes-shield'] = array(
        'module' => 'boxes',
        'delta' => 'shield',
        'weight' => '-10',
        'region' => 'header_third',
    );
  }
}
