<?php

namespace Drupal\os_wysiwyg\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\views\Views;

/**
 * Provides a link dialog for text editors.
 *
 * @internal
 */
class OsWysiwygLinkDialog extends FormBase {

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

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="os-wysiwyg-link-dialog-form">';
    $form['#suffix'] = '</div>';

    $form['attributes'] = [
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
    ];
    $form['email']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail'),
      '#default_value' => isset($input['email']) ? $input['email'] : '',
    ];

    $name = 'media_entity_browser';
    $display = 'media_browser_all';

    $view = Views::getView($name);
    if (!empty($view)) {
      $view->setDisplay($display);
      $view->setItemsPerPage(12);
      $view->preExecute();
      $build = $view->preview();

      // Allow the View title to override the plugin title.
      if ($title = $view->getTitle()) {
        $build['#title'] = $title;
      }

      $form['media'] = [
        '#type' => 'details',
        '#title' => $this->t('File'),
        '#group' => 'link_to',
      ];
      $form['media']['library'] = $build;
      $form['media']['library']['#tree'] = TRUE;;
    }

    if (isset($input['type']) && isset($form[$input['type']])) {
      $form['link_to']['#default_tab'] = 'edit-' . $input['type'];
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
        'callback' => '::submitForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
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
      $response->addCommand(new EditorDialogSave($form_state->getValues()));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

}
