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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->groupAdmin = $this->createUser();
    $this->group = $this->createGroup();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests publication style is changed on citation full view on settings alter.
   */
  public function testCitationFullView(): void {
    $contributor = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => '',
      'last_name' => 'Vinci',
    ]);

    $reference = $this->createReference([
      'html_title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_year' => [],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');

    /** @var \Drupal\Core\Config\Config $bibcite_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut->set('default_style', 'ieee');
    $publication_settings_mut->save();

    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);
    $ama_citation = $this->getActualHtml();

    $this->visitViaVsite('cp/settings/publications', $this->group);
    $this->submitForm(['os_publications_preferred_bibliographic_format' => 'apa'], 'edit-submit');
    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);

    $apa_citation = $this->getActualHtml();
    $this->assertNotSame($ama_citation, $apa_citation);
  }

  /**
   * Tests publication style is changed as per the settings on view page.
   */
  public function testCitationOnViewPage(): void {
    $contributor = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => '',
      'last_name' => 'Vinci',
    ]);

    $reference = $this->createReference([
      'html_title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_year' => [],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');

    /** @var \Drupal\Core\Config\Config $bibcite_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut->set('default_style', 'ieee');
    $publication_settings_mut->save();

    $this->visitViaVsite('publications/', $this->group);
    $ama_citation = $this->getActualHtml();

    $this->visitViaVsite('cp/settings/publications', $this->group);
    $this->submitForm(['os_publications_preferred_bibliographic_format' => 'apa'], 'edit-submit');
    $this->visitViaVsite('publications/', $this->group);

    $apa_citation = $this->getActualHtml();
    $this->assertNotSame($ama_citation, $apa_citation);
  }

}
