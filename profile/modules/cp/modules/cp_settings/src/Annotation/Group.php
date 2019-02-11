<?php

namespace Drupal\cp_settings\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Group annotation.
 *
 * @ingroup plugin_context
 *
 * @Annotation
 */
class Group extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The group title.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * Group parent.
   *
   * @var string
   */
  public $parent;

}
