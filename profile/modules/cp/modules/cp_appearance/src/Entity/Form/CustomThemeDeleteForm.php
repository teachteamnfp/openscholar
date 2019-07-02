<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Custom theme delete form.
 */
final class CustomThemeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete %name custom theme?', [
      '%name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t("The theme %name will be disabled and uninstalled. If this theme is set as default, then it's parent theme will be set as default. All of the theme files will be deleted. This action cannot be undone. Are you sure you want to proceed?", [
      '%name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('cp.appearance');
  }

}
