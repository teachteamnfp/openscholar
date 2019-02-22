<?php

namespace Drupal\os_widgets\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Os Widget annotion.
 *
 * @Annotation
 */
class OsWidget extends Plugin {
  /**
   * The widget plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *   Translation.
   * @ingroup plugin_translatable
   */
  public $title;

}
