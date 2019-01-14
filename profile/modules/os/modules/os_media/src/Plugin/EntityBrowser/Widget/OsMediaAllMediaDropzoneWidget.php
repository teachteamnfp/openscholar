<?php

namespace Drupal\os_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\dropzonejs\DropzoneJsUploadSaveInterface;
use Drupal\dropzonejs_eb_widget\Plugin\EntityBrowser\Widget\DropzoneJsEbWidget;
use Drupal\entity_browser\WidgetValidationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides an Entity Browser widget that uploads and edit new files.
 *
 * @EntityBrowserWidget(
 *   id = "os_media_widget",
 *   label = @Translation("OpenScholar Media upload integration"),
 *   description = @Translation("OpenScholar's take on an Upload widget."),
 *   auto_select = FALSE
 * )
 */
class OsMediaAllMediaDropzoneWidget extends DropzoneJsEbWidget {

  /** @var ModuleHandlerInterface */
  protected $moduleHandler;

  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, WidgetValidationManager $validation_manager, DropzoneJsUploadSaveInterface $dropzonejs_upload_save, AccountProxyInterface $current_user, Token $token, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_type_manager, $validation_manager, $dropzonejs_upload_save, $current_user, $token);
    $this->moduleHandler = $moduleHandler;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_browser.widget_validation'),
      $container->get('dropzonejs.upload_save'),
      $container->get('current_user'),
      $container->get('token'),
      $container->get('module_handler')
    );
  }


  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    // @todo Remove this when/if EB provides a way to define dependencies.
    if (!$this->moduleHandler->moduleExists('inline_entity_form')) {
      return [
        '#type' => 'container',
        'error' => [
          '#markup' => $this->t('Missing requirement: in order to use this widget you have to install Inline entity form module first'),
        ],
      ];
    }

    $form['#attached']['library'][] = 'dropzonejs_eb_widget/ief_edit';
    $form['edit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit'),
      '#attributes' => [
        'class' => ['js-hide'],
      ],
      '#ajax' => [
        'wrapper' => 'ief-dropzone-upload',
        'callback' => [static::class, 'onEdit'],
        'effect' => 'fade',
      ],
      '#submit' => [
        [$this, 'submitEdit'],
      ],
    ];

    $form['entities']['#prefix'] = '<div id="ief-dropzone-upload">';
    $form['entities']['#suffix'] = '</div>';

    $form += ['entities' => []];
    if ($entities = $form_state->get('uploaded_entities')) {
      foreach ($entities as $entity) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $form['entities'][$entity->uuid()] = [
          '#type' => 'inline_entity_form',
          '#entity_type' => $entity->getEntityTypeId(),
          '#bundle' => $entity->bundle(),
          '#default_value' => $entity,
          '#form_mode' => $this->configuration['form_mode'],
        ];
      }
    }

    if (!empty(Element::children($form['entities']))) {
      // Make it possible to select those submitted entities.
      $pos = array_search('dropzonejs-disable-submit', $original_form['#attributes']['class']);
      if ($pos !== FALSE) {
        unset($original_form['#attributes']['class'][$pos]);
      }
    }

    $form['actions']['submit'] += ['#submit' => []];

    return $form;
  }

  /**
   * Submit callback for the edit button.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form object.
   */
  public function submitEdit(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);

    // Files have to saved before they can be viewed in the IEF form.
    $media_entities = $this->prepareEntities($form, $form_state);
    $source_field = $this->getType()->getSource()->getSourceFieldDefinition($this->getType())->getName();
    foreach ($media_entities as $media_entity) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $media_entity->$source_field->entity;
      $file->save();
      $media_entity->$source_field->target_id = $file->id();
    }

    $form_state->set('uploaded_entities', $media_entities);
  }

  /**
   * Ajax callback triggered when hitting the edit button.
   *
   * @param array $form
   *   The form.
   *
   * @return array
   *   Returns the entire form.
   */
  public static function onEdit(array $form) {
    return $form['widget']['entities'];
  }
}