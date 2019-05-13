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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\ImmutableConfig $bibcite_settings */
    $bibcite_settings = $this->configFactory->get('bibcite.settings');
    $this->defaultBibciteCitationStyle = $bibcite_settings->get('default_style');
    $this->citationStyler = $this->container->get('bibcite.citation_styler');
  }

  /**
   * Tests whether publication style is changed as per the settings.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testReferenceStyle() {
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

    $this->visit('/publications');
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit('/publications/title');
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit('/publications/author');
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));

    $this->visit('/publications/year');
    $this->assertSession()->responseContains($this->citationStyler->render($reference_as_stdclass));
  }

  /**
   * Check anonymous user access to publications.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAnonymousUserAccess(): void {
    $this->visit('/publications');

    $this->assertSession()->statusCodeEquals(403);
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
