<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * PublicationsViewsFunctionalTest.
 *
 * @group functional
 * @group publications
 */
class PublicationsViewsFunctionalTest extends TestBase {

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
   * Default publication sort category.
   *
   * @var string
   */
  protected $defaultSortCategory;

  /**
   * Default publication sort order.
   *
   * @var string
   */
  protected $defaultSortOrder;

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
    /** @var \Drupal\Core\Config\ImmutableConfig $publication_settings */
    $publication_settings = $this->configFactory->get('os_publications.settings');
    $this->defaultSortCategory = $publication_settings->get('biblio_sort');
    $this->defaultSortOrder = $publication_settings->get('biblio_order');

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Tests whether publication style is changed as per the settings.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testReferenceStyle(): void {
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
   * Tests sorting in author display.
   *
   * @coversDefaultClass \Drupal\os_publications\Plugin\views\field\AuthorLastNameFirstLetter
   * @coversDefaultClass \Drupal\os_publications\Plugin\views\sort\AuthorLastNameFirstLetter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testAuthorSort(): void {
    // Setup.
    $contributor1 = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => 'Da',
      'last_name' => 'Vinci',
    ]);

    $contributor2 = $this->createContributor([
      'first_name' => 'Joanne',
      'middle_name' => 'Kathleen',
      'last_name' => 'Rowling',
    ]);

    $contributor3 = $this->createContributor([
      'first_name' => 'Vincent',
      'middle_name' => 'van',
      'last_name' => 'Gogh',
    ]);

    $contributor4 = $this->createContributor([
      'first_name' => 'Rabindranath',
      'last_name' => 'Tagore',
    ]);

    $reference1 = $this->createReference([
      'title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor1->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference1, 'group_entity:bibcite_reference');

    $reference2 = $this->createReference([
      'title' => 'Harry Potter and the Deathly Hallows',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor2->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Bloomsbury',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference2, 'group_entity:bibcite_reference');

    $reference3 = $this->createReference([
      'title' => 'Harry Potter and the Chamber of Secrets',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor2->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Bloomsbury',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference3, 'group_entity:bibcite_reference');

    $reference4 = $this->createReference([
      'title' => 'Sorrow',
      'author' => [
        'target_id' => $contributor3->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference4, 'group_entity:bibcite_reference');

    $reference5 = $this->createReference([
      'title' => 'Wheatfield with Crows',
      'author' => [
        'target_id' => $contributor3->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference5, 'group_entity:bibcite_reference');

    $reference6 = $this->createReference([
      'title' => 'Shesher Kobita',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor4->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Some Indian Publisher',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference6, 'group_entity:bibcite_reference');

    $reference7 = $this->createReference([
      'title' => 'Ghare Baire ',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor4->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Some Indian Publisher',
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference7, 'group_entity:bibcite_reference');

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $os_publications_settings_mut */
    $os_publications_settings_mut = $config_factory->getEditable('os_publications.settings');
    $os_publications_settings_mut
      ->set('biblio_order', 'DESC')
      ->set('biblio_sort', 'author')
      ->save();

    // Tests.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('publications/author', $this->group);

    // Confirm that the grouping order is as per the setting.
    /** @var \Behat\Mink\Element\NodeElement[] $groupings */
    $groupings = $this->getSession()->getPage()->findAll('css', '.view-publications .view-content h3');

    $this->assertEquals('V', $groupings[0]->getText());
    $this->assertEquals('T', $groupings[1]->getText());
    $this->assertEquals('R', $groupings[2]->getText());
    $this->assertEquals('G', $groupings[3]->getText());

    // Confirm that the ordering within groups is unchanged by setting.
    /** @var \Behat\Mink\Element\NodeElement[] $rows */
    $rows = $this->getSession()->getPage()->findAll('css', '.view-publications .view-content .views-row');

    $this->assertContains('Mona Lisa', $rows[0]->getText());
    $this->assertContains('Ghare Baire', $rows[1]->getText());
    $this->assertContains('Shesher Kobita', $rows[2]->getText());
    $this->assertContains('Harry Potter and the Chamber of Secrets', $rows[3]->getText());
    $this->assertContains('Harry Potter and the Deathly Hallows', $rows[4]->getText());
    $this->assertContains('Sorrow', $rows[5]->getText());
    $this->assertContains('Wheatfield with Crows', $rows[6]->getText());
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

    /** @var \Drupal\Core\Config\Config $publication_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut
      ->set('biblio_sort', $this->defaultSortCategory)
      ->set('biblio_order', $this->defaultSortOrder)
      ->save();

    parent::tearDown();
  }

}
