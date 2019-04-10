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
   * The id of vsite where preview was initiated.
   *
   * @var int
   */
  protected $vsiteId;

  /**
   * ThemePreview constructor.
   *
   * @param string $name
   *   Name of the theme which is going to be previewed.
   * @param int $vsite_id
   *   The id of vsite where preview would be activated.
   */
  public function __construct(string $name, int $vsite_id) {
    $this->name = $name;
    $this->vsiteId = $vsite_id;
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
  public function getVsiteId(): int {
    return $this->vsiteId;
  }

}
