<?php

namespace Drupal\Tests\os_metatag\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests os_metatag module.
 *
 * @group metatag
 * @group functional
 */
class HtmlHeadOsMetatagTest extends ExistingSiteBase {

  /**
   * Tests os_metatag on empty config not render link rel.
   */
  public function testHtmlHeadOnEmptyConfig() {
    $web_assert = $this->assertSession();

    $this->visit("/");
    $web_assert->statusCodeEquals(200);
    $expectedHtmlValue = '<link rel="publisher"';
    $this->assertNotContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head contains publisher link.');
    $expectedHtmlValue = '<link rel="author"';
    $this->assertNotContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head contains author link.');
  }

}
