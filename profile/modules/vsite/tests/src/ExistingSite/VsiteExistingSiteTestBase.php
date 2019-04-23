<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Symfony\Component\CssSelector\CssSelectorConverter;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Base class for vsite tests.
 */
abstract class VsiteExistingSiteTestBase extends ExistingSiteBase {

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
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   */
  protected function createGroup(array $values = []) {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
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
  protected function cssSelectToXpath($selector, $html = TRUE, $prefix = 'descendant-or-self::') {
    return (new CssSelectorConverter($html))->toXPath($selector, $prefix);
  }

}
