<?php

namespace Drupal\os_pages\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Pages app.
 *
 * @App(
 *   title = @Translation("Pages"),
 *   canDisable = false,
 *   entityType = "node",
 *   bundle = {
 *     "page"
 *   },
 *   id = "page"
 * )
 */
class PagesApp extends AppPluginBase {

}
