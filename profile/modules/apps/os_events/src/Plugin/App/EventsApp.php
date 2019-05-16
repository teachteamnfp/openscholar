<?php

namespace Drupal\os_events\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Events app.
 *
 * @App(
 *   title = @Translation("Event"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "events"
 *   },
 *   id = "event"
 * )
 */
class EventsApp extends AppPluginBase {

}
