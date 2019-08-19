<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Render\Markup;

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
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testReferenceStyle(): void {
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

    // Construct data array as required by render method.
    $text = Markup::create($reference->html_title->value);
    $link = $reference->toLink($text)->toString();
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

    $this->citationStyler->setStyleById('apa');
    $render = $this->citationStyler->render($data);
    $expected = preg_replace('/\s*/m', '', $render);

    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $this->submitForm(['os_publications_preferred_bibliographic_format' => 'apa'], 'edit-submit');

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications");
    $actual = $this->getActualHtml();
    $this->assertContains($actual, $expected);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/title");
    $actual = $this->getActualHtml();
    $this->assertContains($actual, $expected);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/author");
    $actual = $this->getActualHtml();
    $this->assertContains($actual, $expected);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/year");
    $actual = $this->getActualHtml();
    $this->assertContains($actual, $expected);

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
      'html_title' => 'Mona Lisa',
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
      'html_title' => 'Harry Potter and the Deathly Hallows',
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
      'html_title' => 'Harry Potter and the Chamber of Secrets',
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
      'html_title' => 'Sorrow',
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
      'html_title' => 'Wheatfield with Crows',
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
      'html_title' => 'Shesher Kobita',
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
      'html_title' => 'Ghare Baire ',
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

    $this->drupalLogin($this->groupAdmin);

    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $edit = [
      'os_publications_preferred_bibliographic_format' => 'harvard_chicago_author_date',
      'edit-biblio-order' => 'DESC',
      'edit-biblio-sort' => 'author',
    ];
    $this->submitForm($edit, 'edit-submit');

    // Tests.
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
    $this->assertContains('Harry Potter And The Chamber Of Secrets', $rows[3]->getText());
    $this->assertContains('Harry Potter And The Deathly Hallows', $rows[4]->getText());
    $this->assertContains('Sorrow', $rows[5]->getText());
    $this->assertContains('Wheatfield With Crows', $rows[6]->getText());
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
   * Tests whether the publication sort setting is updated in the UI.
   *
   * @covers \Drupal\os_publications\Plugin\CpSetting\PublicationSettingsForm::submitForm
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicationsSortSetting(): void {
    // Setup.
    $reference1 = $this->createReference([
      'title' => 'Mona Lisa',
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference1, 'group_entity:bibcite_reference');

    $reference2 = $this->createReference([
      'title' => 'The Rust Programming Language',
      'type' => 'journal',
      'bibcite_year' => [
        'value' => 2010,
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    $this->group->addContent($reference2, 'group_entity:bibcite_reference');

    $this->drupalLogin($this->groupAdmin);

    $this->visitViaVsite('publications', $this->group);

    // Note the position og groupings.
    $headings = array_map(function (NodeElement $element) {
      return $element->getText();
    }, $this->getSession()->getPage()->findAll('css', '.view-publications .view-content h3'));

    $pos_artwork = array_search('Artwork', $headings, TRUE);
    $pos_journal = array_search('Journal', $headings, TRUE);

    // Make changes.
    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_preferred_bibliographic_format' => 'harvard_chicago_author_date',
      'biblio_sort' => 'type',
      'biblio_order' => 'ASC',
      'os_publications_export_format[bibtex]' => 'bibtex',
      'os_publications_export_format[endnote8]' => 'endnote8',
      'os_publications_export_format[endnote7]' => 'endnote7',
      'os_publications_export_format[tagged]' => 'tagged',
      'os_publications_export_format[ris]' => 'ris',
    ], 'Save configuration');

    // Tests.
    $this->visitViaVsite('publications', $this->group);

    $headings = array_map(function (NodeElement $element) {
      return $element->getText();
    }, $this->getSession()->getPage()->findAll('css', '.view-publications .view-content h3'));

    $this->assertNotEquals($pos_artwork, array_search('Artwork', $headings, TRUE));
    $this->assertNotEquals($pos_journal, array_search('Journal', $headings, TRUE));
  }

  /**
   * Tests link creation from publication edit page.
   */
  public function testPublicationMenuLinkAdd(): void {

    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('bibcite/reference/add/journal_article', $this->group);
    $edit = [
      'bibcite_year[0][value]' => '2019',
      'bibcite_secondary_title[0][value]' => 'Journal Link',
      'menu[enabled]' => TRUE,
      'menu[title]' => 'Menu Link title',
    ];
    $this->submitForm($edit, 'Save');

    $this->assertSession()->linkExists('Menu Link title');
  }

  /**
   * Tests last updated on appears on citation full view.
   */
  public function testLastUpdatedOn(): void {
    $this->drupalLogin($this->groupAdmin);
    $reference = $this->createReference([
      'html_title' => 'Mona Lisa',
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);
    $this->assertSession()->elementExists('css', '.last-updated');
  }

  /**
   * Test hca publication output.
   */
  public function testHcaPublicationsOutput(): void {
    $this->drupalLogin($this->groupAdmin);

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
      'middle_name' => 'Middle',
      'last_name' => 'Gogh',
    ]);

    $reference = $this->createReference([
      'type' => 'journal_article',
      'html_title' => 'Mona Lisa',
      'bibcite_year' => '2018',
      'bibcite_secondary_title' => 'JournalTitle',
      'author' => [
        [
          'target_id' => $contributor1->id(),
          'category' => 'primary',
          'role' => 'author',
        ],
        [
          'target_id' => $contributor2->id(),
          'category' => 'primary',
          'role' => 'author',
        ],
        [
          'target_id' => $contributor3->id(),
          'category' => 'primary',
          'role' => 'editor',
        ],
      ],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');

    // Make changes.
    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_preferred_bibliographic_format' => 'harvard_chicago_author_date',
    ], 'Save configuration');
    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);
    $this->assertSession()->pageTextContains('Leonardo Da Vinci and Joanne Kathleen Rowling. 2018. “Mona Lisa”. Edited By Vincent Middle Gogh. Journaltitle.');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $publication_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut
      ->set('biblio_sort', $this->defaultSortCategory)
      ->set('biblio_order', $this->defaultSortOrder)
      ->save();

    parent::tearDown();
  }

}
