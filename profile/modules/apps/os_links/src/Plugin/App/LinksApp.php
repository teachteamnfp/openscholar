<?php

namespace Drupal\os_links\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Plugin for the Links App.
 *
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "link",
 *   },
 *   id = "links"
 * )
 */
class LinksApp extends AppPluginBase {

}
