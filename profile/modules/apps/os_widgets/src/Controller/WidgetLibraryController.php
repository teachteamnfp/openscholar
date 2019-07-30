<?php

namespace Drupal\os_widgets\Controller;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\StatusMessages;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Methods for managing a site's Widget Library.
 */
class WidgetLibraryController extends ControllerBase {

  /**
   * Load the entity form for a custom block.
   *
   * @param string|\Drupal\block_content\BlockContentTypeInterface $block_type
   *   The type of block being created.
   *
   * @return array
   *   The form.
   */
  public function createBlock($block_type) {
    if (is_string($block_type)) {
      $block_type = $this->entityTypeManager()->getStorage('block_content_type')->load($block_type);
    }

    if (!($block_type instanceof BlockContentType)) {
      throw new NotFoundHttpException("No block type '$block_type' found.");
    }

    $entity = $this->entityTypeManager()->getStorage('block')->create(['plugin' => $block_type->id()]);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Returns ajax commands after the block is saved.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State for the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax commands.
   */
  public static function ajaxSubmitSave(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    if ($form_state->getErrors()) {
      $messages = StatusMessages::renderMessages(NULL);
      $output[] = $messages;
      $output[] = $form;
      $form_class = '.' . str_replace(['content_', '_'], ['', '-'], $form_state->getFormObject()->getFormId()) ;
      // Remove any previously added error messages.
      $response->addCommand(new RemoveCommand('#drupal-modal .messages--error'));
      // Replace old form with new one and with error message.
      $response->addCommand(new ReplaceCommand($form_class, $output));
    }
    elseif ($block_content = $form_state->getFormObject()->getEntity()) {
      $instances = $block_content->getInstances();
      if (!$instances) {
        $plugin_id = 'block_content:' . $block_content->uuid();
        $block_id = 'block_content|' . $block_content->uuid();
        $block = \Drupal::entityTypeManager()->getStorage('block')->create(['plugin' => $plugin_id, 'id' => $block_id]);
        $block->save();
      }
      else {
        $block = reset($instances);
      }
      $block_markup = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
      $markup = [
        '#type' => 'inline_template',
        '#template' => '<div class="block" data-block-id="{{ id }}"><h3 class="block-title">{{ title }}</h3>{{ content }}</div>',
        '#context' => [
          'id' => $block->id(),
          'title' => $block->label(),
          'content' => $block_markup,
        ],
      ];

      $response->addCommand(new PrependCommand('#block-list', $markup));
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new InvokeCommand('#block-list', 'sortable', ['refresh']));
      $response->addCommand(new InvokeCommand('#factory-wrapper .close', 'click'));

    }

    return $response;
  }

  /**
   * Return Ajax commands after editting a widget.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State for the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax commands.
   */
  public static function ajaxSubmitEdit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    if ($block_content = $form_state->getFormObject()->getEntity()) {
      $instances = $block_content->getInstances();
      $block = reset($instances);

      $block_markup = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);

      $response->addCommand(new ReplaceCommand('Section[data-quickedit-entity-id="block_content/' . $block_content->id() . '"]', $block_markup));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Return Ajax commands after deleting a widget.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State for the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax commands.
   */
  public static function ajaxDelete(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $response->addCommand(new CloseModalDialogCommand());

    return $response;
  }

}
