<?php

namespace Drupal\Tests\os_publications\Traits;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\KeywordInterface;
use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;

/**
 * OsPublications test helpers.
 */
trait OsPublicationsTestTrait {

  /**
   * Creates a reference.
   *
   * @param array $values
   *   (Optional) Default values for the reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   The new reference entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createReference(array $values = []) : ReferenceInterface {
    $reference = Reference::create($values + [
      'title' => $this->randomMachineName(),
      'type' => 'artwork',
      'bibcite_year' => [
        'value' => 1980,
      ],
      'distribution' => [
        [
          'value' => 'citation_distribute_repec',
        ],
      ],
      'status' => [
        'value' => 1,
      ],
    ]);

    $reference->save();

    $this->markEntityForCleanup($reference);

    return $reference;
  }

  /**
   * Creates a contributor.
   *
   * @param array $values
   *   (Optional) Default values for the contributor.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface
   *   The new contributor entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContributor(array $values = []) : ContributorInterface {
    $contributor = Contributor::create($values + [
      'first_name' => $this->randomMachineName(),
      'middle_name' => $this->randomMachineName(),
      'last_name' => $this->randomMachineName(),
    ]);

    $contributor->save();

    $this->markEntityForCleanup($contributor);

    return $contributor;
  }

  /**
   * Creates a keyword.
   *
   * @param array $values
   *   (Optional) Default values for the keyword.
   *
   * @return \Drupal\bibcite_entity\Entity\KeywordInterface
   *   The new keyword entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createKeyword(array $values = []) : KeywordInterface {
    $keyword = Keyword::create($values + [
      'name' => $this->randomMachineName(),
    ]);

    $keyword->save();

    $this->markEntityForCleanup($keyword);

    return $keyword;
  }

  /**
   * Returns the rdf file template path.
   *
   * The path is already URI prefixed, i.e. prefixed with `public://`.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference whose template path to be obtained.
   *
   * @return string
   *   The template path.
   */
  protected function getRepecTemplatePath(ReferenceInterface $reference): string {
    /** @var \Drupal\repec\RepecInterface $repec */
    $repec = $this->container->get('repec');

    $serie_directory_config = $repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    return "$directory/$file_name";
  }

}
