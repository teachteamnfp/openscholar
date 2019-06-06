<?php

namespace Drupal\os_widgets\Controller;


use Drupal\block\BlockRepositoryInterface;
use Drupal\block\Entity\Block;
use Drupal\block_content\BlockContentInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenOffCanvasDialogCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WidgetLibraryController extends ControllerBase {


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

  public function editBlock($id) {

  }

  public static function ajaxSubmitSave(&$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    /** @var BlockContentInterface $block_content */
    if ($block_content = $form_state->getFormObject()->getEntity()) {

      $instances = $block_content->getInstances();
      if (!$instances) {
        $plugin_id = 'block_content:'.$block_content->uuid();
        $block_id = 'block_content|'.$block_content->uuid();
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
          'content' => $block_markup
        ]
      ];
      //$markup = '<div class="block" data-block-id="'.$block->id().'"><h3 class="block-title">'.$block->label().'</h3>'.$block_markup.'</div>';
      $response->addCommand(new PrependCommand('#block-list', $markup));
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new InvokeCommand('#block-list', 'sortable', ['refresh']));
      $response->addCommand(new InvokeCommand('#factory-wrapper .close', 'click'));

    }

    return $response;
  }

}
