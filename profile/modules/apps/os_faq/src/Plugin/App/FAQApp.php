<?php

namespace Drupal\os_faq\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * FAQ app.
 *
 * @App(
 *   title = @Translation("FAQ"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *    "faq"
 *   },
 *   id = "faq"
 * )
 */
class FAQApp extends AppPluginBase {

}
