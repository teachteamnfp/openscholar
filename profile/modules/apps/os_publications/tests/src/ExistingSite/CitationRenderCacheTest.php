<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * CitationRenderCacheTest.
 *
 * @group functional
 * @group publications
 */
class CitationRenderCacheTest extends OsExistingSiteTestBase {

  /**
   * Default bibcite citation style.
   *
   * @var array
   */
  protected $defaultBibciteCitationStyle;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Citation styler.
   *
   * @var \Drupal\bibcite\CitationStylerInterface
   */
  protected $citationStyler;

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
    /** @var \Drupal\Core\Config\ImmutableConfig $bibcite_settings */
    $bibcite_settings = $this->configFactory->get('bibcite.settings');
    $this->defaultBibciteCitationStyle = $bibcite_settings->get('default_style');
    $this->citationStyler = $this->container->get('bibcite.citation_styler');

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Tests publication style is changed as per the settings.
   */
  public function testCitationOnFullView(): void {
    $contributor = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => '',
      'last_name' => 'Vinci',
    ]);

    $reference = $this->createReference([
      'title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_year' => [],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $bibcite_settings_mut */
    $bibcite_settings_mut = $config_factory->getEditable('bibcite.settings');
    $bibcite_settings_mut->set('default_style', 'american_medical_association');
    $bibcite_settings_mut->save();

    // Construct data array as required by render method.
    $text = $reference->label();
    $link = '"' . $reference->toLink($text)->toString() . '"';
    $author = [
      'category' => "primary",
      'role' => "author",
      'family' => $contributor->getLastName(),
      'given' => $contributor->getFirstName(),
    ];
    $data = [
      'title' => $link,
      'author' => [$author],
    ];

    $this->drupalLogin($this->groupAdmin);

    $expected = trim($this->citationStyler->render($data));

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications");
    $actual = $this->getActualHtml();
    $this->assertSame($expected, $actual);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/title");
    $actual = $this->getActualHtml();
    $this->assertSame($expected, $actual);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/author");
    $actual = $this->getActualHtml();
    $this->assertSame($expected, $actual);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/year");
    $actual = $this->getActualHtml();
    $this->assertSame($expected, $actual);

    $this->drupalLogout();
  }

  /**
   * Check anonymous user access to publications.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAnonymousUserAccess(): void {
    $this->visit('/publications');

    $this->assertSession()->statusCodeEquals(403);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications");

    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Returns a particular section of the html page.
   *
   * @return string
   *   The row html to compare.
   */
  private function getActualHtml(): string {
    $page = $this->getCurrentPage();
    $row = $page->find('css', '.bibcite-citation');
    $row_html = $row->getHtml();
    // Strip Purl from the html for proper comparison as render method won't
    // return it.
    return trim(str_replace("$this->groupAlias", '', $row_html));

  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $bibcite_settings_mut */
    $bibcite_settings_mut = $this->configFactory->getEditable('bibcite.settings');
    $bibcite_settings_mut
      ->set('default_style', $this->defaultBibciteCitationStyle)
      ->save();

    parent::tearDown();
  }

}
