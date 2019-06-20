<?php

/**
 * @file
 * A bootstrap file for `phpunit` test runner.
 *
 * This bootstrap file from DTT is fast and customizable.
 *
 * If you get 'class not found' 'during test running, you may add copy and add
 * the missing namespaces to bottom of this file. Then specify that file for
 * PHPUnit bootstrap.
 *
 * Alternatively, use the bootstrap.php file in this same directory which
 * is slower
 * but registers all the namespaces that Drupal tests expect.
 */

use weitzman\DrupalTestTraits\AddPsr4;

list($finder, $class_loader) = AddPsr4::add();
$root = $finder->getDrupalRoot();

// Register more namespaces, as needed.
$class_loader->addPsr4('Drupal\Tests\book\Functional\\', "$root/core/modules/book/tests/src/Functional");
$class_loader->addPsr4('Drupal\Tests\rest\Functional\EntityResource\\', "$root/core/modules/rest/tests/src/Functional/EntityResource");
$class_loader->addPsr4('Drupal\Tests\rest\Functional\ResourceTestBase\\', "$root/core/modules/rest/tests/src/Functional/ResourceTestBase");
$class_loader->addPsr4('Drupal\Tests\os_classes\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/apps/os_classes/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_classes\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/apps/os_classes/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_classes\Traits\\', "$root/profiles/contrib/openscholar/modules/apps/os_classes/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\os_events\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/apps/os_events/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_events\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/apps/os_events/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_events\Traits\\', "$root/profiles/contrib/openscholar/modules/apps/os_events/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\os_pages\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/apps/os_pages/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_pages\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/apps/os_pages/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_publications\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/apps/os_publications/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_publications\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/apps/os_publications/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_publications\Traits\\', "$root/profiles/contrib/openscholar/modules/apps/os_publications/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\os_widgets\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/apps/os_widgets/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_widgets\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/apps/os_widgets/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_widgets\Unit\\', "$root/profiles/contrib/openscholar/modules/apps/os_widgets/tests/src/Unit");
$class_loader->addPsr4('Drupal\Tests\cp_appearance\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_appearance/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\cp_appearance\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_appearance/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\cp_appearance\Traits\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_appearance/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\cp_taxonomy\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_taxonomy/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\cp_taxonomy\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_taxonomy/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\cp_roles\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_users/modules/cp_roles/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\cp_roles\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_users/modules/cp_roles/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\cp_roles\Traits\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_users/modules/cp_roles/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\cp_users\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/cp/modules/cp_users/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\cp\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/cp/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\group_entity\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/group_entity/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_fullcalendar\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/os_fullcalendar/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_fullcalendar\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/custom/os_fullcalendar/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_mailchimp\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/os_mailchimp/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_mailchimp\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/custom/os_mailchimp/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_metatag\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/os_metatag/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_metatag\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/custom/os_metatag/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_redirect\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/os_redirect/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_redirect\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/custom/os_redirect/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_twitter_pull\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/custom/os_twitter_pull/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_breadcrumbs\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_breadcrumbs/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_google_analytics\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_google_analytics/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_rest\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_rest/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_rest\Unit\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_rest/tests/src/Unit");
$class_loader->addPsr4('Drupal\Tests\os_theme_preview\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_theme_preview/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\os_theme_preview\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_theme_preview/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\os_theme_preview\Traits\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_theme_preview/tests/src/Traits");
$class_loader->addPsr4('Drupal\Tests\os_theme_preview\Unit\\', "$root/profiles/contrib/openscholar/modules/os/modules/os_theme_preview/tests/src/Unit");
$class_loader->addPsr4('Drupal\Tests\vsite_infinite_scroll\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/vsite/modules/vsite_infinite_scroll/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\vsite_infinite_scroll\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/vsite/modules/vsite_infinite_scroll/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\vsite_privacy\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/vsite/modules/vsite_privacy/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\vsite\ExistingSite\\', "$root/profiles/contrib/openscholar/modules/vsite/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\vsite\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/modules/vsite/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\vsite\Unit\\', "$root/profiles/contrib/openscholar/modules/vsite/tests/src/Unit");
$class_loader->addPsr4('Drupal\Tests\openscholar\ExistingSite\\', "$root/profiles/contrib/openscholar/tests/src/ExistingSite");
$class_loader->addPsr4('Drupal\Tests\openscholar\ExistingSiteJavascript\\', "$root/profiles/contrib/openscholar/tests/src/ExistingSiteJavascript");
$class_loader->addPsr4('Drupal\Tests\openscholar\Traits\\', "$root/profiles/contrib/openscholar/tests/src/Traits");
