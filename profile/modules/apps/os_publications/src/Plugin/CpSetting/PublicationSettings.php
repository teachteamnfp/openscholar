<?php

namespace Drupal\os_publications\Plugin\CpSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bibcite\CitationStylerInterface;
use Drupal\bibcite\Plugin\BibciteFormatManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\os_publications\Plugin\SampleCitations;

/**
 * CP setting.
 *
 * @CpSetting(
 *   id = "publication_setting",
 *   title = @Translation("Publication Setting Form"),
 *   group = {
 *    "id" = "publications",
 *    "title" = @Translation("Publications"),
 *    "parent" = "cp.settings"
 *   }
 * )
 */
class PublicationSettings extends PluginBase implements CpSettingInterface, ContainerFactoryPluginInterface {

  /**
   * The styler service.
   *
   * @var \Drupal\bibcite\CitationStylerInterface
   */
  protected $styler;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    CitationStylerInterface $styler,
    BibciteFormatManagerInterface $format_manager,
    QueryFactory $entityQuery,
  SampleCitations $citations) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->styler = $styler;
    $this->formatManager = $format_manager;
    $this->entityQuery = $entityQuery;
    $this->citations = $citations;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('bibcite.citation_styler'),
      $container->get('plugin.manager.bibcite_format'),
      $container->get('entity.query'),
      $container->get('os_publications.citation_examples')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() : array {
    return [
      'publication.settings',
      'bibcite.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $cite_example_output = $this->citations->citeExampleOutput();
    $publication_config = $configFactory->get('os_publications.settings');
    $csl_styles = $this->styler->getAvailableStyles();
    $styles_options = array_map(function ($entity) {
      /** @var \Drupal\bibcite\Entity\CslStyleInterface $entity */
      return $entity->label();
    }, $csl_styles);

    $form['os_publications_preferred_bibliographic_format'] = [
      '#type' => 'radios',
      '#title' => t('Preferred bibliographic format'),
      '#default_value' => $this->styler->getStyle()->id(),
      '#weight' => -1,
      '#options' => $styles_options,
    ];

    // Citation Examples.
    $form['os_publications_citation_examples'] = [
      '#markup' => $cite_example_output,
      '#weight' => 0,
      '#prefix' => '<div id="citation-examples">',
      '#suffix' => '</div>',
      // '#column' => 'top_right',.
    ];

    $query = $this->entityQuery->get('bibcite_reference_type');
    $options = array_keys($query->execute());
    $publication_types_options = array_map(function ($str) {
      return ucwords(str_replace("_", " ", $str));
    }, $options);
    $publication_types_options = array_combine($options, $publication_types_options);

    $form['os_publications_filter_publication_types'] = [
      '#type' => 'checkboxes',
      '#title' => 'Display on Your Publication Page',
      '#description' => 'Selected publications types will appear on your Publications page. Unselected publication types can still be added to other locations on your site using widgets.',
      '#select_all' => TRUE,
      '#options' => $publication_types_options,
      '#weight' => 0,
      '#sorted_options' => TRUE,
    ];

    $form['os_publications_note_in_teaser'] = [
      '#type' => 'checkbox',
      '#title' => t('Show note content in teaser'),
      '#default_value' => $publication_config->get('os_publications_note_in_teaser'),
      '#weight' => 0,
      '#prefix' => '<label>Notes</label>',
    ];

    $form['biblio_sort'] = [
      '#type' => 'select',
      '#title' => t("Sort By Category"),
      '#default_value' => $publication_config->get('biblio_sort'),
      '#options' => [
        'author' => t('Author'),
        'title' => t('Title'),
        'type' => t('Type'),
        'year' => t('Year'),
      ],
      '#weight' => 0,
    ];

    $form['biblio_order'] = [
      '#type' => 'select',
      '#default_value' => $publication_config->get('biblio_order'),
      '#options' => array('DESC' => t('Descending'), 'ASC' => t('Ascending')),
      '#weight' => 0,
      '#title' => t('Sort Order'),
    ];

    $form['os_publications_shorten_citations'] = [
      '#type' => 'checkbox',
      '#title' => t('Include Short URLs in citations'),
      '#default_value' => $publication_config->get('os_publications_shorten_citations'),
      '#weight' => 2,
      '#prefix' => '<label>Short URLs</label>',
    ];

    $form['os_publications_export_format'] = [
      '#title' => t('Export format'),
      '#type' => 'checkboxes',
      '#default_value' => $publication_config->get('os_publications_export_format'),
      '#options' => array_map(function ($format) {
        return $format['label'];
      }, $this->formatManager->getExportDefinitions()),
    ];

    $form['citation_distribute_autoflags'] = [
      '#type' => 'checkboxes',
      '#title' => t('Distribute to repositories'),
      '#options' => ['test' => 'dummy'],
      // @todo distribution repositroy options
    ];

    $form['#attached']['library'][] = 'os_publications/drupal.os_publications';
    $form['#attached']['drupalSettings']['default_style'] = $this->styler->getStyle()->id();

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $bibcite_config = $configFactory->getEditable('bibcite.settings');
    $publication_config = $configFactory->getEditable('os_publications.settings');
    $bibcite_config
      ->set('default_style', $formState->getValue('os_publications_preferred_bibliographic_format'))
      ->save();
    $publication_config
      ->set('biblio_sort', $formState->getValue('biblio_sort'))
      ->set('os_publications_note_in_teaser', $formState->getValue('os_publications_note_in_teaser'))
      ->set('biblio_order', $formState->getValue('biblio_order'))
      ->set('os_publications_shorten_citations', $formState->getValue('os_publications_shorten_citations'))
      ->set('os_publications_export_format', $formState->getValue('os_publications_export_format'))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    return AccessResult::allowed();
  }

}
