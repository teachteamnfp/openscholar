<?php

namespace Drupal\os_theme_preview;

/**
 * A data type for storing the data related to theme preview.
 */
class ThemePreview implements ThemePreviewInterface {

  /**
   * Name of the theme being previewed.
   *
   * @var string
   */
  protected $name;

  /**
   * The base path where preview was initiated.
   *
   * @var string
   */
  protected $basePath;

  /**
   * ThemePreview constructor.
   *
   * @param string $name
   *   Name of the theme which is going to be previewed.
   * @param string $base_path
   *   The base path where the preview would be activated.
   */
  public function __construct(string $name, string $base_path) {
    $this->name = $name;
    $this->basePath = $base_path;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getBasePath(): string {
    return $this->basePath;
  }

}
