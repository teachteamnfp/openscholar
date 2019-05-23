<?php

namespace Drupal\os_wysiwyg\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\os_wysiwyg\OsLinkHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a link dialog for text editors.
 *
 * @internal
 */
class OsWysiwygLinkDialog extends FormBase {


  /**
   * Os Link Helper.
   *
   * @var \Drupal\os_wysiwyg\OsLinkHelperInterface
   */
  protected $osLinkHelper;

  /**
   * OsWysiwygLinkDialog constructor.
   *
   * @param \Drupal\os_wysiwyg\OsLinkHelperInterface $os_link_helper
   *   Os Link Helper.
   */
  public function __construct(OsLinkHelperInterface $os_link_helper) {
    $this->osLinkHelper = $os_link_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('os_wysiwyg.os_link_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os_wysiwyg_link_dialog';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\editor\Entity\Editor $editor
   *   The text editor to which this dialog corresponds.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, Editor $editor = NULL) {
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    $user_input = $form_state->getUserInput();
    $input = isset($user_input['editor_object']) ? $user_input['editor_object'] : [];

    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="os-wysiwyg-link-dialog-form">';
    $form['#suffix'] = '</div>';

    $form['attributes'] = [
      '#tree' => TRUE,
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text To Display'),
        '#description' => $this->t('The text of the link. Leave blank to use the url of the link itself.'),
        '#default_value' => isset($input['text']) ? $input['text'] : '',
      ],
      'title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Title Text'),
        '#description' => $this->t('This text will appear on mouse hover and is used by screen readers, however, it will not appear for keyboard-only users or touch-only users.'),
        '#default_value' => isset($input['title']) ? $input['title'] : '',
      ],
    ];

    $form['link_to'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Link To'),
    ];
    $form['web_address'] = [
      '#type' => 'details',
      '#title' => $this->t('Web address'),
      '#group' => 'link_to',
      '#id' => 'edit-os-link-to-web-address',
    ];
    $form['web_address']['href'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website URL'),
      '#description' => $this->t('The URL of the web page. Can be a link to one of your own pages.'),
      '#maxlength' => 500,
      '#default_value' => isset($input['url']) ? $input['url'] : '',
    ];
    $form['web_address']['target_option'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open this link in a new tab'),
      '#description' => $this->t('Note: depending on the usage, may cause accessibility concerns. <a href="@link" target="_blank">Learn more</a>', ['@link' => 'https://www.w3.org/TR/WCAG20-TECHS/G200.html']),
      '#default_value' => isset($input['is_blank']) ? $input['is_blank'] : 0,
    ];
    $form['email'] = [
      '#type' => 'details',
      '#title' => $this->t('Email'),
      '#group' => 'link_to',
      '#id' => 'edit-os-link-to-email',
    ];
    $form['email']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail'),
      '#default_value' => isset($input['email']) ? $input['email'] : '',
    ];

    $form['media'] = [
      '#type' => 'details',
      '#title' => $this->t('File'),
      '#group' => 'link_to',
      '#tree' => FALSE,
      '#id' => 'edit-os-link-to-media',
    ];

    $form['media']['entity_browser'] = [
      '#type' => 'entity_browser',
      '#entity_browser' => 'os_link_media_select',
      '#cardinality' => 1,
      '#selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
      '#entity_browser_validators' => [
        'entity_type' => [
          'type' => 'media',
        ],
      ],
      '#widget_context' => [],
      '#custom_hidden_id' => 'original_selected_media',
      '#process' => [
        [EntityBrowserElement::class, 'processEntityBrowser'],
      ],
    ];
    $form['media']['original_selected_media'] = [
      '#type' => 'hidden',
      '#default_value' => isset($input['mid']) ? $input['mid'] : 0,
    ];

    if (isset($input['type']) && isset($form[$input['type']])) {
      $form['link_to']['#default_tab'] = 'edit-os-link-to-' . $input['type'];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitDialogForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * Form ajax dialog submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function submitDialogForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#editor-link-dialog-form', $form));
    }
    else {
      $selected_media_values = array_filter($form_state->getValue('entity_browser'));
      $id = '';
      if (!empty($selected_media_values['entity_ids'])) {
        list(, $id) = explode(':', $selected_media_values['entity_ids']);
      }
      if (empty($id) && !empty($form_state->getValue('original_selected_media'))) {
        $id = $form_state->getValue('original_selected_media');
      }

      $js_data = [
        'attributes' => $form_state->getValue('attributes'),
        'href' => $form_state->getValue('href'),
        'email' => $form_state->getValue('email'),
        'selectedMedia' => $id,
        'selectedMediaUrl' => !empty($id) ? $this->osLinkHelper->getFileUrlFromMedia($id) : '',
        'activeTab' => $form_state->getValue('link_to__active_tab'),
        'targetOption' => $form_state->getValue('target_option'),
      ];
      $response->addCommand(new EditorDialogSave($js_data));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

}
