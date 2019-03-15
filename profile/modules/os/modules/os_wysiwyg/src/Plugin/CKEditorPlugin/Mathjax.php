<?php

namespace Drupal\os_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginBase;

/**
 * Defines the "Mathjax" plugin.
 *
 * @CKEditorPlugin(
 *   id = "mathjax",
 *   label = @Translation("CKEditor MathJax formulae"),
 *   module = "os_software"
 * )
 */
class Mathjax extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getLibraryPath() . '/plugin.js';
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
    return [];
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
  public function getButtons() {
    $path = $this->getLibraryPath();

    return [
      'Mathjax' => [
        'label' => t('Mathjax'),
        'image' => $path . '/icons/mathjax.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * Get the CKEditor Link library path.
   *
   * @return string
   *   The library path with support for the Libraries API module.
   */
  protected function getLibraryPath() {
    // Support for "Libraries API" module.
    if (\Drupal::moduleHandler()->moduleExists('libraries')) {
      return libraries_get_path('mathjax');
    }

    return 'libraries/mathjax';
  }

}
