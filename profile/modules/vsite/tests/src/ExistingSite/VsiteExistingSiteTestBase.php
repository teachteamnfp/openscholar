<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Symfony\Component\CssSelector\CssSelectorConverter;

/**
 * Base class for vsite tests.
 */
abstract class VsiteExistingSiteTestBase extends OsExistingSiteTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Translates a CSS expression to its XPath equivalent.
   *
   * The search is relative to the root element (HTML tag normally) of the page.
   *
   * UIHelper Trait expects this method to be here.
   * Copied from BrowserTestBase
   *
   * @param string $selector
   *   CSS selector to use in the search.
   * @param bool $html
   *   (optional) Enables HTML support. Disable it for XML documents.
   * @param string $prefix
   *   (optional) The prefix for the XPath expression.
   *
   * @return string
   *   The equivalent XPath of a CSS expression.
   */
  protected function cssSelectToXpath($selector, $html = TRUE, $prefix = 'descendant-or-self::'): string {
    return (new CssSelectorConverter($html))->toXPath($selector, $prefix);
  }

}
