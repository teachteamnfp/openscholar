<?php

namespace Drupal\os_media\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Medi Browser" plugin.
 *
 * @CKEditorPlugin(
 *   id = "media_browser",
 *   label = @Translation("CKEditor Media Browser Button")
 * )
 */
class OsMedia extends PluginBase implements CKEditorPluginInterface, CKEditorPluginButtonsInterface {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'MediaBrowser' => [
        'label' => t('Embed Media'),
        'image' => drupal_get_path('module', 'os_media') . '/wysiwyg_plugin/icons/media.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'os_media/mediaBrowser',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'os_media') . '/wysiwyg_plugin/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
