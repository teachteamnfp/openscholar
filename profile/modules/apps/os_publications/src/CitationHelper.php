<?php

namespace Drupal\os_publications;

use Drupal\bibcite\Plugin\BibciteFormatManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Class CitationHelper.
 *
 * @package Drupal\os_publications
 */
class CitationHelper implements CitationHelperInterface {
  use StringTranslationTrait;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Format manager service.
   *
   * @var \Drupal\bibcite\Plugin\BibciteFormatManagerInterface
   */
  protected $formatManager;

  /**
   * CitationHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\bibcite\Plugin\BibciteFormatManagerInterface $format_manager
   *   Format manager instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, BibciteFormatManagerInterface $format_manager) {
    $this->configFactory = $config_factory;
    $this->pubConfig = $this->configFactory->get('os_publications.settings');
    $this->formatManager = $format_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getCitationDownloadButton($entity_id = NULL): ?array {
    // Get vsite settings.
    $formats = $this->pubConfig->get('export_format');

    // Build dropdown button.
    $exportButton = [
      '#theme' => 'item_list__os_dropdown',
      '#title' => [
        '#type' => 'button',
        '#value' => $this->t('Download Citation'),
        '#attributes' => [
          'class' => ['citation-download'],
          'data-toggle' => 'dropdown',
          'role' => 'button',
          'aria-haspopup' => 'true',
          'aria-expanded' => 'false',
        ],
      ],
      '#list_type' => 'ul',
      '#attributes' => [
        'class' => ['dropdown-menu'],
      ],
      '#wrapper_attributes' => [
        'class' => ['dropdown'],
      ],
      '#weight' => 100,
    ];

    // Determine if single export or multiple.
    $routeName = 'os_publictions.citation_export_multiple';
    if ($entity_id) {
      $routeName = 'os_publictions.citation_export';
    }

    foreach ($formats as $key => $format) {
      if ($format === $key) {
        $args['bibcite_format'] = $format;
        // If single export , send the entity id as well as a parameter.
        $entity_id ? $args['bibcite_reference'] = $entity_id : NULL;
        $formatObj = $this->formatManager->createInstance($key);
        $exportButton['#items'][] = [
          '#type' => 'link',
          '#title' => $formatObj->getLabel(),
          '#url' => Url::fromRoute($routeName, $args),
        ];
      }
    }
    return isset($exportButton['#items']) ? $exportButton : NULL;
  }

}
