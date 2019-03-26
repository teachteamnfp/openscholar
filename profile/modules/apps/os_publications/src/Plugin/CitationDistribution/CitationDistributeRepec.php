<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os_publications\CitationDistributionException;
use Drupal\os_publications\GhostEntity\Repec;
use Drupal\os_publications\GhostEntityInterface;
use Drupal\repec\RepecInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Citation Distribute GoogleScholar service.
 *
 * @CitationDistribute(
 *   id = "citation_distribute_repec",
 *   title = @Translation("RePEc citation distribute service."),
 *   name = "RePEc",
 *   href = "https://repec.org",
 *   description = "Searchable index of citations in RePEc",
 * )
 */
class CitationDistributeRepec extends PluginBase implements CitationDistributionInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Repec service.
   *
   * @var \Drupal\repec\RepecInterface
   */
  protected $repec;

  /**
   * CitationDistributeRepec constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\repec\RepecInterface $repec
   *   Repec service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RepecInterface $repec) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->repec = $repec;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('repec'));
  }

  /**
   * {@inheritdoc}
   */
  public function render($id): array {
    // Repec does not renders anything.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function mapMetadata($id): array {
    // The mapping is handled by the repec module.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity): bool {
    if (!$entity instanceof ContentEntityInterface) {
      return TRUE;
    }

    try {
      if ($this->repec->isBundleEnabled($entity) && $this->repec->isEntityShareable($entity)) {
        $this->repec->createEntityTemplate($entity);
      }
    }
    catch (\Exception $e) {
      throw new CitationDistributionException($this->t('Could not create citation. Error: %message. Backtrace: @trace', [
        '%message' => $e->getMessage(),
        '@trace' => print_r($e->getTrace(), TRUE),
      ]));
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(GhostEntityInterface $entity) {
    try {
      $this->deleteEntityTemplate($entity);
    }
    catch (\Exception $e) {
      throw new CitationDistributionException($this->t('Could not delete citation. Error: %message. Backtrace: @trace', [
        '%message' => $e->getMessage(),
        '@trace' => print_r($e->getTrace(), TRUE),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function killEntity(EntityInterface $entity): GhostEntityInterface {
    return new Repec($entity->id(), $entity->getEntityTypeId(), $entity->bundle());
  }

  /**
   * Deletes the rdf template.
   *
   * @param \Drupal\os_publications\GhostEntityInterface $entity
   *   The necessary data required for deletion.
   *
   * @see \Drupal\repec\Repec::deleteEntityTemplate
   */
  protected function deleteEntityTemplate(GhostEntityInterface $entity) {
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $entity->type(), $entity->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$entity->type()}_{$entity->id()}.rdf";
    $file_path = "$directory/$file_name";

    Assert::assertFileExists($file_path);

    file_unmanaged_delete($file_path);
  }

  /**
   * {@inheritdoc}
   */
  public function createGhostEntityFromPayload(array $payload): GhostEntityInterface {
    return new Repec($payload['id'], $payload['type'], $payload['bundle']);
  }

}
