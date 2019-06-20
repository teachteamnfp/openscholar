<?php

namespace Drupal\os_news\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * News app.
 *
 * @App(
 *   title = @Translation("News"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *    "news"
 *   },
 *   id = "news"
 * )
 */
class NewsApp extends AppPluginBase {

}
