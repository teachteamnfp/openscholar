<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Citation Distribute GoogleScholar service.
 *
 * @CitationDistribute(
 *   id = "citation_distribute_googlescholar",
 *   title = @Translation("Google scholar citation distribute service."),
 *   type = "metadata",
 *   name = "Google Scholar",
 *   href = "https://scholar.google.com",
 *   description = "Google's searchable index of citations",
 * )
 */
class CitationDistributeGooglescholar implements CitationDistributionInterface, ContainerFactoryPluginInterface {

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save($id, $plugin) : bool {
    /*
     * google_scholar themes a node if it has an entry in {citation_distribute}
     * with type=google_scholar to reach this point that must have happened, so
     * the change is already saved.
     */
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function mapMetadata($id) : array {
    $entity = $this->entityTypeManager->getStorage('bibcite_reference')->load($id);
    $keywords_arr = [];
    $contributors_arr = [];

    $metadata = [
      'citation_journal_title' => 'bibcite_secondary_title',
      'citation_publisher' => 'bibcite_publisher',
      'citation_title' => 'title',
      'citation_year' => 'bibcite_year',
      'citation_volume' => 'bibcite_volume',
      'citation_issue' => 'bibcite_issue',
      'citation_issn' => 'bibcite_issn',
      'citation_isbn' => 'bibcite_isbn',
      'citation_language' => 'bibcite_lang',
      'citation_abstract' => 'bibcite_abst_e',
      'citation_abstract_html_url' => 'bibcite_url',
    ];

    foreach ($metadata as $key => $value) {
      $metadata[$key] = (isset($entity->get($value)->value)) ? htmlspecialchars(strip_tags($entity->get($value)->value), ENT_COMPAT, 'ISO-8859-1', FALSE) : NULL;
    }

    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $reference */
    foreach ($entity->get('keywords') as $reference) {
      $target_id = $reference->getValue()['target_id'];
      /** @var \Drupal\bibcite_entity\Entity\KeywordInterface $keyword_obj */
      $keyword_obj = $this->entityTypeManager->getStorage('bibcite_keyword')->load($target_id);
      $keywords_arr[] = $keyword_obj->name->value;
    }

    $metadata['citation_keywords'] = htmlspecialchars(strip_tags(implode(';', $keywords_arr)), ENT_COMPAT, 'ISO-8859-1', FALSE);

    if (isset($entity->bibcite_year, $entity->bibcite_date)) {
      $metadata['citation_publication_date'] = $this->googleScholarDate($entity->bibcite_year->value, $entity->bibcite_date->value);
    }

    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $reference */
    foreach ($entity->get('author') as $reference) {
      $target_id = $reference->getValue()['target_id'];
      /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $contributor_obj */
      $contributor_obj = $this->entityTypeManager->getStorage('bibcite_contributor')->load($target_id);
      $contributors_arr[] = $contributor_obj->name->value;
    }
    $metadata['citation_author'] = $this->googleScholarListAuthors($contributors_arr);

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function render($id) : array {
    $metadata = array_filter($this->mapMetadata($id));
    $output = [];
    foreach ($metadata as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $subvalue) {
          $output[] = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => $key,
              'content' => $subvalue,
            ],
          ];
        }
      }
      else {
        $output[] = [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => $key,
            'content' => $value,
          ],
        ];
      }
    }
    return $output;
  }

  /**
   * Returns array of author names formatted for google scholar.
   *
   * @param array $contributors
   *   Authors.
   *
   * @return array
   *   Authors List.
   */
  protected function googleScholarListAuthors(array $contributors = []) : array {
    $authors = [];
    foreach ($contributors as $cont) {
      $authors[] = htmlspecialchars(strip_tags($cont), ENT_COMPAT, 'ISO-8859-1', FALSE);
    }
    return $authors;
  }

  /**
   * Returns $date in YYYY/M/D if possible. just year if not.
   *
   * @param int $year
   *   Year.
   * @param int $date
   *   Date.
   *
   * @return string
   *   Date/Year.
   */
  protected function googleScholarDate($year, $date) : string {
    if ($date) {
      return date('Y/m/d', strtotime($date)) ?? '';
    }

    if ($year) {
      return $year;
    }

    return '';
  }

}
