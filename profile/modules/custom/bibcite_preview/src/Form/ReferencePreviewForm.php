<?php

namespace Drupal\bibcite_preview\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reference Preview Form.
 */
class ReferencePreviewForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_display.repository'),
      $container->get('config.factory')
    );
  }

  /**
   * Constructs a new ReferencePreviewForm.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(EntityDisplayRepositoryInterface $entity_display_repository, ConfigFactoryInterface $config_factory) {
    $this->entityDisplayRepository = $entity_display_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reference_preview_form_select';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Entity\EntityInterface $reference
   *   The reference being previews.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, EntityInterface $reference = NULL) {
    $view_mode = $reference->preview_view_mode;

    $query_options = ['query' => ['uuid' => $reference->uuid()]];
    $query = $this->getRequest()->query;
    if ($query->has('destination')) {
      $query_options['query']['destination'] = $query->get('destination');
    }

    $form['#attributes'] = ['class' => ['node-preview-form-select form-inline bg-info text-center clearfix']];

    $form['backlink'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to content editing'),
      '#url' => $reference->isNew() ? Url::fromRoute('entity.bibcite_reference.add_form', ['bibcite_reference_type' => $reference->bundle()]) : $reference->urlInfo('edit-form'),
      '#options' => ['attributes' => ['class' => ['reference-preview-backlink node-preview-backlink btn btn-info pull-left']]] + $query_options,
      "#icon" => [
        '#type' => "html_tag",
        '#tag' => "span",
        '#value' => "",
        '#attributes' => [
          'class' => ["icon", "glyphicon-chevron-left", "glyphicon"],
        ],
      ],
    ];

    // Always show full as an option, even if the display is not enabled.
    $view_mode_options = $this->entityDisplayRepository->getViewModeOptionsByBundle('bibcite_reference', $reference->bundle());

    // Unset view modes that are not used in the front end.
    unset($view_mode_options['default']);
    unset($view_mode_options['rss']);
    unset($view_mode_options['search_index']);
    unset($view_mode_options['table']);

    $form['uuid'] = [
      '#type' => 'value',
      '#value' => $reference->uuid(),
    ];

    $form['view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('View mode'),
      '#options' => $view_mode_options,
      '#default_value' => $view_mode,
      '#attributes' => [
        'data-drupal-autosubmit' => TRUE,
      ],
      '#prefix' => '<div class="pull-right">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Switch'),
      '#attributes' => [
        'class' => ['js-hide'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route_parameters = [
      'bibcite_reference_preview' => $form_state->getValue('uuid'),
      'view_mode_id' => $form_state->getValue('view_mode'),
    ];

    $options = [];
    $query = $this->getRequest()->query;
    if ($query->has('destination')) {
      $options['query']['destination'] = $query->get('destination');
      $query->remove('destination');
    }
    $form_state->setRedirect('entity.bibcite_reference.preview', $route_parameters, $options);
  }

}
