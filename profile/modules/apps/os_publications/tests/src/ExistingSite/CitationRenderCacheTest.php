<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * CitationRenderCacheTest.
 *
 * @group functional
 * @group publications
 */
class CitationRenderCacheTest extends TestBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Publication entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->groupAdmin = $this->createUser();
    $this->group = $this->createGroup();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);

    $contributor = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => '',
      'last_name' => 'Vinci',
    ]);

    $this->reference = $this->createReference([
      'html_title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_year' => [],
    ]);
    $this->group->addContent($this->reference, 'group_entity:bibcite_reference');

    /** @var \Drupal\Core\Config\Config $bibcite_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut->set('default_style', 'ieee');
    $publication_settings_mut->save();
  }

  /**
   * Tests publication style is changed on citation full view on settings alter.
   */
  public function testCitationFullView(): void {

    $this->visitViaVsite('bibcite/reference/' . $this->reference->id(), $this->group);
    $ieee_citation = $this->getActualHtml();

    // Test different styles have different output.
    $this->changeStyle('apa');
    $apa_citation = $this->getActualHtml();
    $this->assertNotSame($ieee_citation, $apa_citation);

    // Test on switching back to previous style gives same output.
    $this->changeStyle('ieee');
    $ieee_citation2 = $this->getActualHtml();
    $this->assertSame($ieee_citation, $ieee_citation2);
  }

  /**
   * Tests publication style is changed as per the settings on view page.
   */
  public function testCitationOnViewPage(): void {

    $this->visitViaVsite('publications/', $this->group);
    $ieee_citation = $this->getActualHtml();

    // Test different styles have different output.
    $this->changeStyle('apa');
    $apa_citation = $this->getActualHtml();
    $this->assertNotSame($ieee_citation, $apa_citation);

    // Test on switching back to previous style gives same output.
    $this->changeStyle('ieee');
    $ieee_citation2 = $this->getActualHtml();
    $this->assertSame($ieee_citation, $ieee_citation2);
  }

  /**
   * Changes vsite style.
   *
   * @param string $style
   *   The style to be set.
   */
  private function changeStyle(string $style): void {
    $this->visitViaVsite('cp/settings/publications', $this->group);
    $this->submitForm(['os_publications_preferred_bibliographic_format' => $style], 'edit-submit');
    $this->visitViaVsite('bibcite/reference/' . $this->reference->id(), $this->group);
  }

}
