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
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\cp_menu\Services\MenuHelper;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu List form.
 */
class MenuBuildForm extends FormBase {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MenuLinkTree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * VsiteContextManager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Menus for this vsite.
   *
   * @var array
   */
  protected $menus;

  /**
   * Cp Menu Helper service.
   *
   * @var \Drupal\cp_menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * The overview tree form.
   *
   * @var array
   */
  protected $overviewTreeForm = ['#tree' => TRUE];

  /**
   * MenuBuildForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   Menu Link tree instance.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   Vsite Context Manager instance.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   Menu Link Manager instance.
   * @param \Drupal\cp_menu\MenuHelperInterface $menu_helper
   *   Menu Helper interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, VsiteContextManager $vsite_manager, MenuLinkManagerInterface $menu_link_manager, MenuHelperInterface $menu_helper) {
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->vsiteManager = $vsite_manager;
    $this->menuLinkManager = $menu_link_manager;
    $this->menuHelper = $menu_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('menu.link_tree'),
      $container->get('vsite.context_manager'),
      $container->get('plugin.manager.menu.link'),
      $container->get('cp_menu.menu_helper')
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

    $vsite = $this->vsiteManager->getActiveVsite();
    $menus = $vsite->getContent('group_menu:menu');

    if (!$menus) {
      $this->menus = [
        'main' => 'Primary Menu',
        'footer' => 'Secondary Menu',
      ];
      $revert = array_keys($this->menus);
    }
    elseif ($menus) {
      foreach ($menus as $menu) {
        $this->menus[$menu->entity_id_str->target_id] = $menu->label();
        $revert = array_keys($this->menus);
        $revert = count($revert) > 2 ? array_slice($revert, 0, 2) : $revert;
      }
    }

    $this->vsiteAlias = '/' . $this->vsiteManager->getActivePurl();
    $headers = [
      $this->t('Title'),
      $this->t('Url'),
      $this->t('Edit'),
      $this->t('Delete'),
      $this->t('Menu'),
      $this->t('Weight'),
    ];

    $form['add_new'] = [
      '#title' => $this->t('Add new menu'),
      '#type' => 'link',
      '#url' => Url::fromRoute('cp.build.add_menu'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
          'button--primary',
          'button-action',
          'new-menu',
        ],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode(['width' => '50%']),
        'id' => 'add_new_menu',
      ],
    ];

    $form['links'] = [
      '#type' => 'table',
      '#theme' => 'table__menu_overview',
      '#header' => $headers,
      '#attributes' => [
        'id' => 'cp-build-menu-table',
      ],
    ];

    $weight = 5;

    foreach ($this->menus as $m => $menu) {
      $form['links']['#tabledrag'][] = [
        'action' => 'match',
        'relationship' => 'parent',
        'group' => 'menu-parent',
        'subgroup' => 'menu-parent-' . $m,
        'source' => 'menu-id',
        'hidden' => FALSE,
        'limit' => $this->menuTree->maxDepth() - 1,
      ];
      $form['links']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'menu-weight',
        'subgroup' => 'menu-weight-' . $m,
        'hidden' => FALSE,
      ];

      $tree = $this->menuLoadTree($m);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
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
        '#markup' => $menu,
        'menu-name' => [
          '#type' => 'hidden',
          '#value' => $m,
          '#attributes' => [
            'class' => [
              'menu-name',
            ],
          ],
        ],
        '#wrapper_attributes' => [
          'colspan' => 1,
        ],
      ];
      $form['links'][$m]['title']['#attributes'] = ['class' => ['menu-name']];

      $form['links'][$m]['#weight'] = $weight++;

      $url = Url::fromRoute('cp.build.remove_menu', ['menu_id' => $m, 'label' => $menu], [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => json_encode(['width' => '50%']),
          'id' => 'remove_menu',
        ],
      ]);
      $resetLink = Link::fromTextAndUrl(in_array($m, $revert) ? 'Reset' : 'Remove', $url)->toString();

      $form['links'][$m]['reset'] = [
        '#markup' => $resetLink,
        '#wrapper_attributes' => [
          'colspan' => 3,
        ],
      ];

      $url = Url::fromRoute('cp.build.add_new_link', ['menu' => $m], [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => json_encode(['width' => '50%']),
          'id' => 'add_new_link',
        ],
      ]);
      $newLink = Link::fromTextAndUrl('+ Add new Link', $url)->toString();

      $form['links'][$m]['new_link'] = [
        '#markup' => $newLink,
        '#wrapper_attributes' => [
          'colspan' => 4,
        ],
      ];

      $form['links'][$m]['#attributes'] = ['class' => 'section-heading'];

      $form['links'][$m . '-message'] = [
        '#attributes' => [
          'class' => [
            'section-message',
            'section-' . $m . '-message',
            empty($tree) ? 'section-empty' : 'section-populated',
          ],
        ],
      ];
      $form['links'][$m . '-message']['message'] = [
        '#markup' => $this->t('This menu has no links.'),
        '#wrapper_attributes' => [
          'colspan' => 5,
        ],
      ];

      $links = $this->buildMenuTreeForm($tree, $delta);
      foreach (Element::children($links) as $id) {
        if (isset($links[$id]['#item'])) {
          $element = $links[$id];

          $form['links'][$id]['#item'] = $element['#item'];

          // TableDrag: Mark the table row as draggable.
          $form['links'][$id]['#attributes'] = $element['#attributes'];
          $form['links'][$id]['#attributes']['class'][] = 'draggable';

          // TableDrag: Sort the table row according to its
          // existing/configured weight.
          $form['links'][$id]['#weight'] = $element['#item']->link->getWeight();

          // Add special classes to be used for tabledrag.js.
          $element['parent']['#attributes']['class'] = [
            'menu-parent',
            'menu-parent-' . $m,
          ];
          $element['weight']['#attributes']['class'] = [
            'menu-weight',
            'menu-weight-' . $m,
            'hidden',
          ];
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
      '#value' => $this->t('Save changes'),
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
    $label = [];
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
          $vsite = $this->vsiteManager->getActiveVsite();
          $menus = $vsite->getContent('group_menu:menu');
          // If first time changes , create new menus and map changes.
          if (!$menus) {
            // Create new menus and get the tree for mapping.
            $tree = $this->menuHelper->createVsiteMenus($vsite);
            // Cache the tree in form state as we need plugin ids to map changes
            // Ids will not change, so it is safe to cache this.
            $form_state->set('new_tree', $tree);
          }

          $new_tree = $form_state->get('new_tree') ?? [];
          $pluginId = NULL;
          $old_parent = NULL;
          // Use the ID from the actual plugin instance since the hidden value
          // in the form could be tampered with.
          foreach ($updated_values as $key => $value) {
            if ($key == 'parent' && $value) {
              $old_parent = $this->menuLinkManager->getDefinition($value)['title'];
            }
            if ($key == 'menu_name') {
              $updated_values[$key] = MenuHelper::DEFAULT_VSITE_MENU_MAPPING[$value] . $vsite->id();
              $menu_name[] = $this->menuLinkManager->getDefinition($element['#item']->link->getPluginId())['menu_name'];
            }
          }
          // Map changes to the new tree. It is safe to compare titles as for
          // the first time we always know what those are.
          foreach ($new_tree as $link) {
            if ($old_parent && $link->link->getTitle()->__toString() == $old_parent) {
              $updated_values['parent'] = $link->link->getPluginId();
            }
            if ($element['#item']->link->getTitle() == $link->link->getTitle()) {
              $pluginId = $link->link->getPluginId();
            }
          }
          // If first time changes then use new plugin ids, otherwise use
          // current element ids.
          $pluginId = $pluginId ?? $element['#item']->link->getPluginId();
          $this->menuLinkManager->updateDefinition($pluginId, $updated_values);

          // Get definition to clear cache of the menu block in context.
          $menu_name[] = $this->menuLinkManager->getDefinition($pluginId)['menu_name'];
        }
      }
    }
    // Clear the block cache.
    foreach ($menu_name as $name) {
      $label[] = isset($this->menus[$name]) ? $this->menus[$name] : NULL;
    }
    ($label) ? $this->menuHelper->invalidateBlockCache($vsite, $label, TRUE) : NULL;
  }

  /**
   * Get menu link data by menu name.
   */
  protected function menuLoadTree($menu) : array {
    $treeParams = new MenuTreeParameters();

    // Load common menu links for all vsites.
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
  protected function buildMenuTreeForm(array $tree, $delta) : array {
    $form = &$this->overviewTreeForm;
    foreach ($tree as $element) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      if ($link) {
        $id = 'menu_plugin_id:' . $link->getPluginId();
        $form[$id]['#item'] = $element;
        $form[$id]['#attributes'] = ['class' => [$link->getTitle()]];
        $form[$id]['title'] = Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())->toRenderable();

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
          '#title' => $this->t('edit'),
          '#type' => 'link',
          '#url' => Url::fromRoute('cp.build.edit_menu_link', ['link_id' => $link->getPluginId()]),
          '#attributes' => [
            'class' => ['use-ajax', 'far', 'fa-edit'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode(['width' => '50%']),
            'id' => 'edit_menu_link',
          ],
        ];

        $form[$id]['delete'] = [
          '#title' => $this->t('delete'),
          '#type' => 'link',
          '#url' => Url::fromRoute('cp.build.delete_menu_link', [
            'link_id' => $link->getPluginId(),
            'link_title' => $link->getTitle(),
          ]),
          '#attributes' => [
            'class' => ['use-ajax', 'far', 'fa-trash-alt'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode(['width' => '50%']),
            'id' => 'delete_menu_link',
          ],
        ];

        $form[$id]['menu_name'] = [
          '#type' => 'select',
          '#options' => $this->menus,
          '#default_value' => $link->getMenuName(),
          '#attributes' => [
            'class' => [
              'menu-name',
              'menu-name-' . $link->getMenuName(),
            ],
          ],
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
