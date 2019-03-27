<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\GhostEntity\Repec;

/**
 * CitationDistributionRepecPluginTest.
 *
 * @group kernel
 * @group publications
 * @coversDefaultClass \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributeRepec
 */
class CitationDistributionRepecPluginTest extends TestBase {

  /**
   * Citation distribution plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $citationDistributionPluginManager;

  /**
   * Repec plugin.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface
   */
  protected $repecPlugin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->citationDistributionPluginManager = $this->container->get('os_publications.manager_citation_distribute');
    $this->repecPlugin = $this->citationDistributionPluginManager->createInstance('citation_distribute_repec');
  }

  /**
   * Tests deleteEntityTemplate().
   *
   * @covers ::deleteEntityTemplate
   *
   * @throws \Drupal\os_publications\CitationDistributionException
   */
  public function testDeleteEntityTemplate() {
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', 'bibcite_reference', 'artwork');
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_bibcite_reference_47.rdf";
    $file_path = "$directory/$file_name";

    file_put_contents($file_path, "Handle: RePEc:cde:wpaper:47");

    $ghost_entity = new Repec(47, 'bibcite_reference', 'artwork');

    $this->repecPlugin->delete($ghost_entity);

    $this->assertFileNotExists($file_path);
  }

  /**
   * Tests killEntity().
   *
   * @covers ::killEntity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testKillEntity() {
    $reference = $this->createReference();

    $actual_ghost_entity = $this->repecPlugin->killEntity($reference);
    $expected_ghost_entity = new Repec($reference->id(), $reference->getEntityTypeId(), $reference->bundle());

    $this->assertSame($expected_ghost_entity->id(), $actual_ghost_entity->id());
    $this->assertSame($expected_ghost_entity->type(), $actual_ghost_entity->type());
    $this->assertSame($expected_ghost_entity->bundle(), $actual_ghost_entity->bundle());
  }

  /**
   * Tests createGhostEntityFromPayload().
   *
   * @covers ::createGhostEntityFromPayload
   */
  public function testCreateGhostEntityFromPayload() {
    $payload = [
      'id' => 47,
      'type' => 'bibcite_reference',
      'bundle' => 'artwork',
    ];

    $actual_entity = $this->repecPlugin->createGhostEntityFromPayload($payload);
    $expected_entity = new Repec($payload['id'], $payload['type'], $payload['bundle']);

    $this->assertSame($expected_entity->id(), $actual_entity->id());
    $this->assertSame($expected_entity->type(), $actual_entity->type());
    $this->assertSame($expected_entity->bundle(), $actual_entity->bundle());
  }

}
