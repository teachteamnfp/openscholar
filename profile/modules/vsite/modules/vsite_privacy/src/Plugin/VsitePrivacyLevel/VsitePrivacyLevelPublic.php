<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelPluginBase;

/**
 * Vsite privacy level.
 *
 * @VsitePrivacyLevel(
 *   title = @Translation("Public"),
 *   id = "public",
 *   description = @Translation("Accessible to everyone."),
 *   weight = -1000
 * )
 */
class VsitePrivacyLevelPublic extends VsitePrivacyLevelPluginBase {}
