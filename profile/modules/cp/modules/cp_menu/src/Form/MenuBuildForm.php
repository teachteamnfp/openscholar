<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\vsite\Plugin\VsiteContextManager;
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
   * The overview tree form.
   *
   * @var array
   */
  protected $overviewTreeForm = ['#tree' => TRUE];

  /**
   * Creates a new FlavorForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\os\MenuHelperInterface $menuHelper
   *   Menu Helper.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, VsiteContextManager $vsite_manager, MenuLinkManagerInterface $menu_link_manager) {
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->vsiteManager = $vsite_manager;
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('menu.link_tree'),
      $container->get('vsite.context_manager'),
      $container->get('plugin.manager.menu.link')
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

    $this->menus = $this->configFactory->getEditable('cp_menu.settings')->get('menus');
    $vsiteId = $this->vsiteManager->getActiveVsite()->id();
    if (!$this->menus) {
      $this->menus = [
        'group-menu-' . $vsiteId => 'Primary Menu',
        'group-menu-secondary-' . $vsiteId => 'Secondary Menu'
      ];
    }

    $this->vsiteAlias =  '/' . $this->vsiteManager->getActivePurl();
    $headers = [
      $this->t('Title'),
      $this->t('Url'),
      $this->t('Edit'),
      $this->t('Delete'),
      $this->t('Menu'),
      $this->t('Weight')
    ];

    $revert = [
      'group-menu-' . $vsiteId,
      'group-menu-secondary-' . $vsiteId,
    ];

    $form['links'] = [
      '#type' => 'table',
      '#theme' => 'table__menu_overview',
      '#header' => $headers,
      '#attributes' => [
        'id' => 'menu-overview',
      ],
    ];

    foreach ($this->menus as $m => $menu) {

      $form['links']['#tabledrag'][] = [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'menu-parent',
          'subgroup' => 'menu-parent',
          'source' => 'menu-id',
          'hidden' => FALSE,
          'limit' => $this->menuTree->maxDepth() - 1,
        ];
      $form['links']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'menu-weight',
      ];

      $tree = $this->menuLoadTree($m);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort']
      ];
      $tree = $this->menuTree->transform($tree, $manipulators);

      // Determine the delta; the number of weights to be made available.
      $count = function (array $tree) {
        $sum = function ($carry, MenuLinkTreeElement $item) {
          return $carry + $item->count();
        };
        return array_reduce($tree, $sum);
      };
      $delta = max($count($tree), 50);

      $form['links'][$m]['title'] = [
        '#markup' => $this->t($menu),
        'menu-name' => [
          '#type' => 'hidden',
          '#value' => $m,
          '#attributes' => [
            'class' => [
              'menu-name'
            ]
          ]
        ],
        '#wrapper_attributes' => [
          'colspan' => 1,
        ],
      ];
      $form['links'][$m]['title']['#attributes'] = ['class' => ['menu-name']];

      $form['links'][$m]['reset'] = [
        '#markup' => $removeText = in_array($m, $revert) ? 'Reset' : 'Remove',
        '#wrapper_attributes' => [
          'colspan' => 3,
        ],
      ];
      $form['links'][$m]['new_link'] = [
        '#markup' => '+ Add new Link',
        '#wrapper_attributes' => [
          'colspan' => 2,
        ],
      ];

      $form['links'][$m]['#attributes'] = ['class' => 'section-heading'];

      $links = $this->buildMenuTreeForm($tree, $delta);
        foreach (Element::children($links) as $id) {
          if (isset($links[$id]['#item'])) {
            $element = $links[$id];

            $form['links'][$id]['#item'] = $element['#item'];

            // TableDrag: Mark the table row as draggable.
            $form['links'][$id]['#attributes'] = $element['#attributes'];
            $form['links'][$id]['#attributes']['class'][] = 'draggable';

            // TableDrag: Sort the table row according to its existing/configured weight.
            $form['links'][$id]['#weight'] = $element['#item']->link->getWeight();

            // Add special classes to be used for tabledrag.js.
            $element['parent']['#attributes']['class'] = ['menu-parent'];
            $element['weight']['#attributes']['class'] = ['menu-weight'];
            $element['id']['#attributes']['class'] = ['menu-id'];

            $form['links'][$id]['title'] = [
              [
                '#theme' => 'indentation',
                '#size' => $element['#item']->depth - 1,
              ],
              $element['title'],
            ];
            $form['links'][$id]['link_url'] = $element['link_url'];
            $form['links'][$id]['edit'] = $element['edit'];
            $form['links'][$id]['delete'] = $element['delete'];
            $form['links'][$id]['menu_name'] = $element['menu_name'];
            $form['links'][$id]['weight'] = $element['weight'];
            $form['links'][$id]['id'] = $element['id'];
            $form['links'][$id]['parent'] = $element['parent'];
          }
        }
      }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save changes'),
    ];

    $form['#attached']['library'][] = 'cp_menu/cp_menu.drag';
    return $form;


  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $fields = ['weight', 'parent', 'menu_name'];
    $form_links = $form['links'];
    foreach (Element::children($form_links) as $id) {
      if (isset($form_links[$id]['#item'])) {
        $element = $form_links[$id];
        $updated_values = [];
        // Update any fields that have changed in this menu item.
        foreach ($fields as $field) {
          if ($element[$field]['#value'] != $element[$field]['#default_value']) {
            $updated_values[$field] = $element[$field]['#value'];
          }
        }
        if ($updated_values) {
          // Use the ID from the actual plugin instance since the hidden value
          // in the form could be tampered with.
          $this->menuLinkManager->updateDefinition($element['#item']->link->getPLuginId(), $updated_values);
        }
      }
    }
  }

  /**
   * Get menu link data by menu name
   */
  protected function menuLoadTree($menu) {
    $groupId = $this->vsiteManager->getActiveVsite()->id();
    $treeParams = new MenuTreeParameters();
    $tree = $this->menuTree->load($menu, $treeParams);
    return $tree;
  }

  /**
   * Recursive helper function for buildOverviewForm().
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The tree retrieved by \Drupal\Core\Menu\MenuLinkTreeInterface::load().
   * @param int $delta
   *   The default number of menu items used in the menu weight selector is 50.
   *
   * @return array
   *   The overview tree form.
   */
  protected function buildMenuTreeForm($tree, $delta) {
    $form = &$this->overviewTreeForm;
    foreach ($tree as $element) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      if ($link) {
        $id = 'menu_plugin_id:' . $link->getPluginId();
        $form[$id]['#item'] = $element;
        $form[$id]['#attributes'] = ['class' => ['menu-enabled']];
        $form[$id]['title'] =  Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())->toRenderable();


        // Show the first 80 charcters of the URL.
        $menuLinkText = $link->getUrlObject()->setOption('absolute', TRUE)->toString();
        $urlDisplay = (strlen($menuLinkText) > 80) ? substr($menuLinkText, 0, 80) . "..." : $menuLinkText;

        $form[$id]['link_url'] = [
          '#markup' => Link::fromTextAndUrl($urlDisplay, $link->getUrlObject())->toString(),
        ];

        $form[$id]['id'] = [
          '#type' => 'hidden',
          '#value' => $link->getPluginId(),
        ];
        $form[$id]['parent'] = [
          '#type' => 'hidden',
          '#default_value' => $link->getParent(),
        ];
        $form[$id]['edit'] = [
          '#markup' => Link::fromTextAndUrl('edit', Url::fromUserInput($this->vsiteAlias . '/cp/build/menu/link/' . $link->getPluginId() . '/edit'))
            ->toString(),
        ];
        $form[$id]['delete'] = [
          '#markup' => Link::fromTextAndUrl('delete', Url::fromUserInput($this->vsiteAlias . '/cp/build/menu/link/' . $link->getPluginId() . '/delete'))
            ->toString(),
        ];
        $form[$id]['menu_name'] = [
          '#type' => 'select',
          '#options' => $this->menus,
          '#default_value' => $link->getMenuName(),
          '#attributes' => [
            'class' => [
              'menu-name',
              'menu-name-' . $link->getMenuName()
            ]
          ]
        ];
        $form[$id]['weight'] = [
          '#type' => 'weight',
          '#delta' => $delta,
          '#default_value' => $link->getWeight(),
          '#title' => $this->t('Weight for @title', ['@title' => $link->getTitle()]),
          '#title_display' => 'invisible',
        ];

      }
      if ($element->subtree) {
        $this->buildMenuTreeForm($element->subtree, $delta);
      }
    }
    return $form;
  }
}
