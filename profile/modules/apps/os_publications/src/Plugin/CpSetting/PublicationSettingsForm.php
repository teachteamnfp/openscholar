<?php

namespace Drupal\os_publications\Plugin\CpSetting;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Drupal\os_publications\PublicationsListingHelperInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bibcite\CitationStylerInterface;
use Drupal\bibcite\Plugin\BibciteFormatManagerInterface;
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
 *    "parent" = "cp.settings.app"
 *   }
 * )
 */
class PublicationSettingsForm extends CpSettingBase {

  /**
   * The styler service.
   *
   * @var \Drupal\bibcite\CitationStylerInterface
   */
  protected $styler;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Format Manager.
   *
   * @var \Drupal\bibcite\Plugin\BibciteFormatManagerInterface
   */
  protected $formatManager;

  /**
   * Citation generate.
   *
   * @var \Drupal\os_publications\Plugin\SampleCitations
   */
  protected $citations;

  /**
   * Publications listing helper.
   *
   * @var \Drupal\os_publications\PublicationsListingHelperInterface
   */
  protected $publicationsListingHelper;

  /**
   * Citation distribution plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $pluginManager;

  /**
   * Creates a new PublicationSettingsForm object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\bibcite\CitationStylerInterface $styler
   *   The styler service.
   * @param \Drupal\bibcite\Plugin\BibciteFormatManagerInterface $formatManager
   *   Format Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManager service.
   * @param \Drupal\os_publications\Plugin\SampleCitations $citations
   *   Citation generate.
   * @param \Drupal\os_publications\PublicationsListingHelperInterface $redirect_repository
   *   Publications listing helper.
   * @param \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager $pluginManager
   *   Citation distribution plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager, CitationStylerInterface $styler, BibciteFormatManagerInterface $formatManager, EntityTypeManagerInterface $entityTypeManager, SampleCitations $citations, PublicationsListingHelperInterface $redirect_repository, CitationDistributePluginManager $pluginManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->styler = $styler;
    $this->formatManager = $formatManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->citations = $citations;
    $this->publicationsListingHelper = $redirect_repository;
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.context_manager'),
      $container->get('bibcite.citation_styler'),
      $container->get('plugin.manager.bibcite_format'),
      $container->get('entity_type.manager'),
      $container->get('os_publications.citation_examples'),
      $container->get('os_publications.listing_helper'),
      $container->get('os_publications.manager_citation_distribute')
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
      '#title' => $this->t('Preferred bibliographic format'),
      '#default_value' => $publication_config->get('default_style'),
      '#weight' => -1,
      '#prefix' => '<div class="publication-format">',
      '#suffix' => '</div>',
      '#options' => $styles_options,
    ];

    // Citation Examples.
    $form['os_publications_citation_examples'] = [
      '#markup' => $cite_example_output,
      '#weight' => 0,
      '#prefix' => '<div id="citation-examples" class="citation-format-example">',
      '#suffix' => '</div>',
    ];

    $query = $this->entityTypeManager->getStorage('bibcite_reference_type')->getQuery();
    $options = array_keys($query->execute());
    $publication_types_options = array_map(function ($str) {
      return ucwords(str_replace("_", " ", $str));
    }, $options);
    $publication_types_options = array_combine($options, $publication_types_options);

    $form['os_publications_filter_publication_types'] = [
      '#type' => 'checkboxes',
      '#title' => 'Display on Your Publication Page',
      '#description' => $this->t('Selected publications types will appear on your Publications page. Unselected publication types can still be added to other locations on your site using widgets.'),
      '#default_value' => $publication_config->get('filter_publication_types'),
      '#options' => ['all' => $this->t('Select All')] + $publication_types_options,
      '#weight' => 0,
      '#sorted_options' => TRUE,
      '#prefix' => '<div class="publication-display form-inline">',
      '#suffix' => '</div>',
    ];
    $form['markup_start'] = [
      "#type" => 'markup',
      '#prefix' => '<div class="citation-content-wrapper">',
    ];
    $form['os_publications_note_in_teaser'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show note content in teaser'),
      '#default_value' => $publication_config->get('note_in_teaser'),
      '#prefix' => '<div class="citation-row"><span class="label">' . $this->t('Notes') . '</span>',
    ];

    $form['biblio_sort'] = [
      '#type' => 'select',
      '#title' => $this->t("Sort By Category"),
      '#default_value' => $publication_config->get('biblio_sort'),
      '#options' => [
        'author' => $this->t('Author'),
        'title' => $this->t('Title'),
        'type' => $this->t('Type'),
        'year' => $this->t('Year'),
      ],
    ];

    $form['biblio_order'] = [
      '#type' => 'select',
      '#default_value' => $publication_config->get('biblio_order'),
      '#options' => ['DESC' => $this->t('Descending'), 'ASC' => $this->t('Ascending')],
      '#title' => $this->t('Sort Order'),
      '#suffix' => '</div>',
    ];

    $form['os_publications_shorten_citations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include Short URLs in citations'),
      '#default_value' => $publication_config->get('shorten_citations'),
      '#prefix' => '<div class="citation-row"><span class="label">' . $this->t('Short URLs') . '</span>',
    ];

    $form['os_publications_export_format'] = [
      '#title' => $this->t('Export format'),
      '#type' => 'checkboxes',
      '#default_value' => $publication_config->get('export_format'),
      '#options' => array_map(function ($format) {
        return $format['label'];
      }, $this->formatManager->getExportDefinitions()),
      '#suffix' => '</div>',
    ];

    $plugins = $this->pluginManager->getDefinitions();
    foreach ($plugins as $plugin) {
      $distribution_options[$plugin['id']] = isset($plugin['name']) ? $plugin['name'] : NULL;
    }

    $form['citation_distribute_autoflags'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Distribute to repositories'),
      '#default_value' => $publication_config->get('citation_distribute_autoflags'),
      '#options' => $distribution_options,
      '#prefix' => '<div class="citation-row">',
      '#suffix' => '</div>',
    ];
    $form['markup_end'] = [
      "#type" => 'markup',
      '#prefix' => '</div>',
    ];

    $form['#attached']['library'][] = 'os_publications/drupal.os_publications';
    $form['#attached']['drupalSettings']['default_style'] = $this->styler->getStyle()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $publication_config = $configFactory->getEditable('os_publications.settings');

    // If changes in style then clear citation cache.
    if ($formState->getValue('os_publications_preferred_bibliographic_format') !== $publication_config->get('default_style')) {
      Cache::invalidateTags(['publication_citation', 'config:views.view.publications']);
    }

    $publication_config
      ->set('default_style', $formState->getValue('os_publications_preferred_bibliographic_format'))
      ->set('filter_publication_types', $formState->getValue('os_publications_filter_publication_types'))
      ->set('biblio_sort', $formState->getValue('biblio_sort'))
      ->set('note_in_teaser', $formState->getValue('os_publications_note_in_teaser'))
      ->set('biblio_order', $formState->getValue('biblio_order'))
      ->set('shorten_citations', $formState->getValue('os_publications_shorten_citations'))
      ->set('export_format', $formState->getValue('os_publications_export_format'))
      ->set('citation_distribute_autoflags', $formState->getValue('citation_distribute_autoflags'))
      ->save();

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->vsiteContextManager->getActiveVsite();

    /** @var \Drupal\redirect\Entity\Redirect|null $redirect */
    $redirect = $this->publicationsListingHelper->setRedirect("[vsite:{$group->id()}]/publications", "internal:/publications/{$formState->getValue('biblio_sort')}");

    if ($redirect) {
      $group->addContent($redirect, 'group_entity:redirect');
    }
  }

}
