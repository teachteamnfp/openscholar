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

    $reference_as_stdclass = new \stdClass();
    $reference_as_stdclass->title = $reference->label();
    $reference_as_stdclass->author = json_decode(json_encode([
      [
        'family' => $contributor->getLastName(),
        'given' => $contributor->getFirstName(),
        'category' => 'primary',
        'role' => 'author',
      ],
    ]), FALSE);

    $this->drupalLogin($this->groupAdmin);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications");
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/title");
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/author");
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/publications/year");
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

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
   * Tests publications by year.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testYear(): void {
    // Set up.
    $reference1 = $this->createReference([
      'title' => 'The Starry Night',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    // 25/12/2019.
    $reference1->setCreatedTime(1577232000)->save();
    $this->group->addContent($reference1, 'group_entity:bibcite_reference');

    $reference2 = $this->createReference([
      'title' => 'Foobar',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'is_sticky' => [
        'value' => 0,
      ],
    ]);
    // 29/02/2012.
    $reference2->setCreatedTime(1330473600)->save();
    $this->group->addContent($reference2, 'group_entity:bibcite_reference');

    // Tests.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('publications/year', $this->group);

    /** @var \Behat\Mink\Element\NodeElement[] $rows */
    $rows = $this->getSession()->getPage()->findAll('css', '.view-publications .view-content .views-row');

    $this->assertContains('Foobar', $rows[0]->getHtml());
    $this->assertContains('The Starry Night', $rows[1]->getHtml());
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
