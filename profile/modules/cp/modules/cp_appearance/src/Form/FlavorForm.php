<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\cp_appearance\ThemeSelectorBuilderInterface;
use Ds\Map;

/**
 * Flavor selection form.
 */
class FlavorForm extends FormBase {

  /**
   * The theme for which the form will be created.
   *
   * @var \Drupal\Core\Extension\Extension
   */
  protected $theme;

  /**
   * Available flavors of the theme.
   *
   * Flavor name and its information mapping.
   *
   * @var \Ds\Map
   */
  protected $flavors;

  /**
   * Theme selector builder service.
   *
   * @var \Drupal\cp_appearance\ThemeSelectorBuilderInterface
   */
  protected $themeSelectorBuilder;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Name of the default theme.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * Creates a new FlavorForm object.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme for which the form will be created.
   * @param \Ds\Map $flavors
   *   Available flavors of the theme.
   * @param \Drupal\cp_appearance\ThemeSelectorBuilderInterface $theme_selector_builder
   *   Theme selector builder service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(Extension $theme, Map $flavors, ThemeSelectorBuilderInterface $theme_selector_builder, ConfigFactoryInterface $config_factory) {
    $this->theme = $theme;
    $this->flavors = $flavors;
    $this->themeSelectorBuilder = $theme_selector_builder;
    $this->configFactory = $config_factory;
    /** @var \Drupal\Core\Config\ImmutableConfig $theme_settings */
    $theme_settings = $config_factory->get('system.theme');
    $this->defaultTheme = $theme_settings->get('default');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return "cp_appearance_{$this->theme->getName()}_flavor_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $options = [
      $this->theme->getName() => $this->t('Without Flavor'),
    ];
    /** @var string $default_theme */
    $default_theme = $this->defaultTheme;

    /** @var \Drupal\Core\Extension\Extension $flavor */
    foreach ($this->flavors->values() as $flavor) {
      $options[$flavor->getName()] = $flavor->info['name'];
    }

    $form["options_{$this->theme->getName()}"] = [
      '#type' => 'select',
      '#title' => $this->t('Flavors'),
      '#options' => $options,
      '#default_value' => isset($options[$default_theme]) ? $default_theme : NULL,
      '#ajax' => [
        'callback' => '::flavorChangeHandler',
      ],
    ];

    $form["save_{$this->theme->getName()}"] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#name' => Html::cleanCssIdentifier("save-{$this->theme->getName()}"),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var string $selection */
    $selection = $form_state->getValue("options_{$this->theme->getName()}");
    /** @var \Drupal\Core\Config\Config $theme_settings_mut */
    $theme_settings_mut = $this->configFactory->getEditable('system.theme');

    $theme_settings_mut
      ->set('default', $selection)
      ->save();

    $this->messenger()->addMessage($this->t('Settings have been saved.'));
  }

  /**
   * Flavor option change handler.
   *
   * @ingroup forms
   */
  public function flavorChangeHandler(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    /** @var string $selection */
    $selection = $form_state->getValue("options_{$this->theme->getName()}");
    /** @var string $theme_selector_identifier */
    $theme_selector_identifier = Html::cleanCssIdentifier("theme-selector-{$this->theme->getName()}");
    /** @var string[] $available_flavors */
    $available_flavors = \array_keys($this->theme->sub_themes);
    $default_theme_options = \array_merge([$this->theme->getName()], $available_flavors);

    // Theme operations are going to be rendered differently in case of default
    // theme.
    if (\in_array($this->defaultTheme, $default_theme_options, TRUE)) {
      // Making sure that multiple operations are not added.
      $response->addCommand(new RemoveCommand("#$theme_selector_identifier .theme-info .operations"));

      if ($selection !== $this->defaultTheme) {
        $response->addCommand(new AfterCommand("#$theme_selector_identifier .theme-info__description", [
          '#theme' => 'links',
          '#attributes' => [
            'class' => [
              'operations',
            ],
          ],
          '#links' => [
            [
              'title' => $this->t('Preview'),
              'url' => Url::fromRoute('cp_appearance.preview', [
                'theme' => $selection,
              ]),
            ],
          ],
        ]));
      }
    }
    else {
      if ($selection !== $this->theme->getName()) {
        /** @var \Drupal\Core\Extension\Extension $flavor */
        $flavor = $this->flavors->get($selection);
        /** @var array $info */
        $info = $flavor->info;
        /** @var string|null $screenshot_uri */
        $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($flavor);
      }
      else {
        // Revert everything to normal if user has not chosen a flavor.
        /** @var array $info */
        $info = $this->theme->info;
        /** @var string|null $screenshot_uri */
        $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($this->theme);
      }

      $response->addCommand(new ReplaceCommand("#$theme_selector_identifier .theme-screenshot img", [
        '#theme' => 'image',
        '#uri' => $screenshot_uri ?? '',
        '#alt' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
        '#title' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
        '#attributes' => ['class' => ['screenshot']],
      ]));

      $response->addCommand(new ReplaceCommand("#$theme_selector_identifier .theme-info .operations .preview", [
        '#type' => 'link',
        '#title' => $this->t('Preview'),
        '#url' => Url::fromRoute('cp_appearance.preview', [
          'theme' => $selection,
        ]),
        '#options' => [
          'attributes' => [
            'title' => $this->t('Preview @theme', ['@theme' => $info['name']]),
            'class' => [
              'btn',
              'btn-sm',
              'btn-default',
              'preview',
            ],
            'data-toggle' => 'tooltip',
            'data-placement' => 'bottom',
            'data-original-title' => $this->t('Preview @theme', ['@theme' => $info['name']]),
          ],
        ],
        '#icon' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => '',
          '#attributes' => [
            'class' => ['icon', 'glyphicon', 'glyphicon-eye-open'],
            'aria-hidden' => 'true',
          ],
        ],
      ]));
    }

    return $response;
  }

}
