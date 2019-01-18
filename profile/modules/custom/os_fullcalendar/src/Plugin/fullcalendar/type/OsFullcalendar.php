<?php

namespace Drupal\os_fullcalendar\Plugin\fullcalendar\type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\fullcalendar\Plugin\FullcalendarBase;

/**
 * Fullcalendar customizations for OpenScholar.
 *
 * @FullcalendarOption(
 *   id = "os_fullcalendar",
 *   module = "os_fullcalendar",
 *   js = TRUE
 * )
 */
class OsFullcalendar extends FullcalendarBase {

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    // The actual setting is made inside JavaScript.
    // @see Drupal.fullcalendar.plugins.os_fullcalendar.options
    return [
      'os_fullcalendar' => [
        'contains' => [],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // Currently the options are set directly from JavaScript.
    // Therefore, the form is not implemented.
  }

}
