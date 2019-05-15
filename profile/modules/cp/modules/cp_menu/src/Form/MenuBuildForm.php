<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os\MenuHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu form.
 */
class MenuBuildForm extends FormBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new FlavorForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\os\MenuHelperInterface $menuHelper
   *   Menu Helper.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuHelperInterface $menuHelper) {
    $this->configFactory = $config_factory;
    $this->menuHelper = $menuHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('menu.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return "cp_menu_build";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $menus = $this->menuHelper->osGetMenus();
    $menu = [];

    $is_active = FALSE;
    if (isset($menus[$menu])) {
      $is_active = TRUE;
    }
    $weight = 5;

    $form['menus'] = [
      '#theme' => 'cp_menu_table',
      '#tree' => TRUE,
    ];

    foreach ($menus as $m => $title) {
      $form['menus'][$m] = [
        '#weight' => (($is_active && $menu == $m) ? 1 : $weight++),
        '#hidden' => ($is_active && $menu != $m),
        'menu-name' => [
          '#type' => 'hidden',
          '#value' => $m,
          '#attributes' => [
            'class' => [
              'menu-name',
            ],
          ],
        ],
      ];
    }
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save settings'),
      ],
    ];

    $form['#submit'] = ['cp_menu_submit_form'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

  }

}
