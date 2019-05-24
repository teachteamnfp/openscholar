<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
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

    $menu_array = $this->menuHelper->osGetMenus();
    foreach ($menu_array as $key => $menu) {
      $menus[$key] = \Drupal::entityTypeManager()->getStorage('menu')->load($key);
    }
//
    $weight = 5;
//
//    $form['$menus'] = [
//      '#type' => 'table',
//      '#header' => [
//        t('Title'),
//        t('URL'),
//        t('Edit'),
//        t('Delete'),
//        t('Menu'),
//        t('Weight')
//      ],
//      '#empty' => t('There are no items yet. <a href="@add-url">Add an item.</a>', array(
//        '@add-url' => Url::fromRoute('mymodule.manage_add'),
//      )),
//      // TableDrag: Each array value is a list of callback arguments for
//      // drupal_add_tabledrag(). The #id of the table is automatically prepended;
//      // if there is none, an HTML ID is auto-generated.
//      '#tabledrag' => [
//        [
//          'action' => 'order',
//          'relationship' => 'sibling',
//          'group' => 'mytable-order-weight',
//        ],
//      ],
//    ];
//
//
//
//    foreach ($menus as $m => $menu) {
//      //      $form['$menu_array']['menus'][$m] = [
//      //        '#weight' => $weight++,
//      //        'menu-name' => [
//      //          '#type' => 'hidden',
//      //          '#value' => $m,
//      //          '#attributes' => [
//      //            'class' => [
//      //              'menu-name',
//      //            ],
//      //          ],
//      //        ],
//      //      ];
//      $links[$m] = $this->menuLoadLinks($m);
//    }
//
//      //$linksinks = _cp_menu_flatten_tree(os_menu_tree_data($m));
//      foreach ($links['primary-menu'] as $link) {
//        //Show links as absolute
//        $url = ($link->url) ?? FALSE;
//        $title = unserialize($link->title);
//
//        // Show the first 80 charcters of the URL.
//        $url_display = ($url && strlen($url) > 80) ? substr($url, 0, 80)."...":$url;
//
//
//        // TableDrag: Mark the table row as draggable.
//        $form['$menus']['primary'][$link->mlid]['#attributes']['class'][] = 'draggable';
//        // TableDrag: Sort the table row according to its existing/configured weight.
//        $form['$menus']['primary'][$link->mlid]['#weight'] = $link->weight;
//
//
//        $form['$menus']['primary'][$link->mlid] = [
//          'title' => [
//            '#type' => 'item',
//            '#markup' => '<div class="link-title">' . $title . '</div>',
//          ],
//          'link_href' => [
//            '#markup' => $url ?? Link::fromTextAndUrl('delete', Url::fromUserInput($url))->toString(),
//          ],
//          'delete_link' => [
//            '#markup' => Link::fromTextAndUrl('delete', Url::fromUserInput('/cp/build/menu/link/' . $link->mlid . '/delete'))->toString()
//          ],
//          'edit_link' => [
//            '#markup' => Link::fromTextAndUrl('edit', Url::fromUserInput('/cp/build/menu/link/' . $link->mlid . '/edit'))->toString()
//          ],
//          'menu' => [
//            '#type' => 'select',
//            '#options' => $menu_array,
//            '#default_value' => $link->menu_name,
//            '#attributes' => [
//              'class' => [
//                'menu-name',
//                'menu-name-' . $link->menu_name
//              ]
//            ]
//          ],
//          'weight' => [
//            '#type' => 'weight',
//            '#default_value' => $link->weight,
//            '#attributes' => [
//              'class' => [
//                'menu-weight',
//                'menu-weight-' . $link->menu_name
//              ]
//            ]
//          ],
////          'plid' => [
////            '#type' => 'hidden',
////            '#default_value' => $link->plid,
////            '#attributes' => [
////              'class' => [
////                'menu-plid',
////                'menu-plid-' . $link->menu_name
////              ]
////            ]
////          ],
//          'mlid' => [
//            '#type' => 'hidden',
//            '#default_value' => $link->mlid,
//            '#attributes' => [
//              'class' => [
//                'menu-mlid',
//                'menu-mlid' . $link->menu_name
//              ]
//            ]
//          ],
//          'menu-old' => [
//            '#type' => 'hidden',
//            '#value' => $link->menu_name
//          ],
//          '#depth' => $link->depth
//        ];
//      }
//    $form['actions'] = [
//      '#type' => 'actions',
//      'submit' => [
//        '#type' => 'submit',
//        '#value' => $this->t('Save settings'),
//      ],
//    ];
//
//    $form['#submit'] = ['cp_menu_submit_form'];
//
//    return $form;


    $vsite_alias =  '/' . \Drupal::service('vsite.context_manager')->getActivePurl();

    $form['mytable'] = [
      '#type' => 'table',
      '#header' => [
//        t('Title'),
//        t('URL'),
//        t('Edit'),
//        t('Delete'),
//        t('Menu'),
//        t('Weight')
      ],
      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically prepended;
      // if there is none, an HTML ID is auto-generated.
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
    ];

    $revert = [
      'primary-menu',
      'secondary-menu',
    ];


      $links = $this->menuLoadLinks();

      // Build the table rows and columns.
      // The first nested level in the render array forms the table row, on which you
      // likely want to set #attributes and #weight.
      // Each child element on the second level represents a table column cell in the
      // respective table row, which are render elements on their own. For single
      // output elements, use the table cell itself for the render element. If a cell
      // should contain multiple elements, simply use nested sub-keys to build the
      // render element structure for drupal_render() as you would everywhere else.
      foreach ($links as $id => $link) {
        $m = $link->menu_name;
        if (!isset($form['mytable'][$m])) {
          $form['mytable'][$m]['title'] = [
          '#plain_text' => $m,
          '#attributes' => [
            'class' => [
              'menu-name'
            ],
          ]
        ];
          $removeLinkText = in_array($m, $revert) ? 'Reset' : 'Remove';
          $form['mytable'][$m]['reset'] = [
            '#markup' => Link::fromTextAndUrl($removeLinkText, Url::fromUserInput($vsite_alias . '/cp/build/menu/remove/' . $m))
              ->toString()
          ];
          $form['mytable'][$m]['add_new'] = [
            '#markup' => Link::fromTextAndUrl(t('+ Add new link'), Url::fromUserInput($vsite_alias . '/cp/build/menu/link/new/' . $m))
              ->toString()
          ];
        }

        // TableDrag: Mark the table row as draggable.
        $form['mytable'][$id]['#attributes']['class'][] = 'draggable';
        // TableDrag: Sort the table row according to its existing/configured weight.
        $form['mytable'][$id]['#weight'] = $link->weight;


        $title = unserialize($link->title);
        $options = ['absolute' => TRUE];
        $url = ($link->url) ? $link->url : FALSE;//Url::fromRoute($link->route_name, [], $options);

        // Some table columns containing raw markup.
        $form['mytable'][$id]['title'] = [
          '#plain_text' => $title,
        ];
        $form['mytable'][$id]['link_url'] = [
          '#markup' => $url,
        ];
        $form['mytable'][$id]['edit'] = [
          '#markup' => Link::fromTextAndUrl('edit', Url::fromUserInput($vsite_alias . '/cp/build/menu/link/' . $link->mlid . '/edit'))
            ->toString(),
        ];
        $form['mytable'][$id]['delete'] = [
          '#markup' => Link::fromTextAndUrl('delete', Url::fromUserInput($vsite_alias . '/cp/build/menu/link/' . $link->mlid . '/delete'))
            ->toString(),
        ];
        $form['mytable'][$id]['menu'] = [
          '#type' => 'select',
          '#options' => $menu_array,
          '#default_value' => $link->menu_name,
          '#attributes' => [
            'class' => [
              'menu-name',
              'menu-name-' . $link->menu_name
            ]
          ]
        ];

        // TableDrag: Weight column element.
        // NOTE: The tabledrag javascript puts the drag handles inside the first column,
        // then hides the weight column. This means that tabledrag handle will not show
        // if the weight element will be in the first column so place it further as in this example.
        $form['mytable'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $title]),
          '#title_display' => 'invisible',
          '#default_value' => $link->weight,
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['mytable-order-weight']],
        ];
      }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save changes'),
    ];
    return $form;


  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * Get menu link data by menu name
   */
  public function menuLoadLinks() {
    $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $groupId = \Drupal::service('vsite.context_manager')->getActiveVsite()->id();

      $treeParams = new MenuTreeParameters();
      $treeParams->addCondition('route_name', 'entity.group.canonical');
      // Get the tree.
      $query = \Drupal::database()->select('menu_tree', 'mt');
      $query->fields('mt', []);
      $query->condition('route_param_key', "group=$groupId");
      $links = $query->execute()->fetchAll();
//      $service = \Drupal::service('menu.tree_storage');
//      $linksinks = $service->loadTreeData($menu_name, $treeParams);
//      kint($linksinks);
      return $links;

  }
}
