<?php

namespace Drupal\os_profiles\Plugin\CpSetting;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * OS Profiles Setting.
 *
 * @CpSetting(
 *   id = "profiles_setting",
 *   title = @Translation("Profiles Settings"),
 *   group = {
 *    "id" = "profiles",
 *    "title" = @Translation("Profiles Settings"),
 *    "parent" = "cp.settings.app"
 *   }
 * )
 */
class ProfilesSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['os_profiles.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('os_profiles.settings');

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = \Drupal::getContainer()->get('entity_display.repository');
    $view_modes = $entity_display_repository->getViewModeOptionsByBundle('node', 'person');

    $profile_styles = [];
    foreach ($view_modes as $name => $label) {
      if ($name == 'default') {
        // Display only display modes the user turned on.
        continue;
      }

      $profile_styles[$name] = $label;
    }

    $profile_styles_hover = [];
    $counter = 0;

    // Create markup for style examples when hovering over each style.
    foreach ($profile_styles as $name => $label) {
      $counter++;
      // Prepare the popup and the style name.
      $profile_example_link = '<a href="#" class="profile-pop" data-popbox="pop' . $counter . '">' . Html::escape($label) . '</a>';
      $profile_example_title = '<h2>' . $label . '</h2>';
      // Prepare the profile style example for the popup.
      $profile_example = 'Lorem ipsum';
      $profile_example_text = '<p>' . $profile_example . '</p>';
      $profile_example_output = $profile_example_link . '<div id="pop' . $counter . '" class="stylebox">' . $profile_example_title . $profile_example_text . '</div>';
      $profile_styles_hover[$name] = $profile_example_output;
    }

    $form['display_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display types'),
      '#options' => $profile_styles_hover,
      '#default_value' => $config->get('display_type'),
      '#description' => t('Choose the display type of a person in the "/people" page.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('os_profiles.settings');
    $config->set('display_type', $formState->getValue('display_type'));
    $config->save(TRUE);
  }

}
