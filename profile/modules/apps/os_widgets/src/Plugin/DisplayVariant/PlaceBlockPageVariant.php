<?php

namespace Drupal\os_widgets\Plugin\DisplayVariant;

use Drupal\block\BlockInterface;
use Drupal\block\BlockRepositoryInterface;
use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\block_place\Plugin\DisplayVariant\PlaceBlockPageVariant as OriginalVariant;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;
use Drupal\os_widgets\Entity\LayoutContext;
use Drupal\os_widgets\LayoutContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PlaceBlockPageVariant extends OriginalVariant {

  /** @var SectionStorageManagerInterface */
  protected $sectionStorageManager;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->sectionStorageManager = $container->get('plugin.manager.layout_builder.section_storage');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  // context should be in path
  public function build() {
    $build = parent::build();
    $applicable = LayoutContext::getApplicable();

    $contexts = [];
    foreach ($applicable as $app) {
      $contexts[$app->id()] = $app->label();
    }

    foreach (Element::children($build) as $region) {
      $build[$region]['#attributes']['class'] = 'block-place-region';
      $build[$region]['#attributes']['data-region'] = $region;
      unset($build[$region]['block_place_operations']);
      $build[$region]['placeholder'] = [
        '#type' => 'markup',
        '#markup' => '<div class="block-placeholder"></div>'
      ];
    }

    $context = \Drupal::request()->query->get('context');

    $build['footer_bottom']['widget_selector'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'block-place-widget-selector-wrapper'
      ],
      'markup' => $this->buildWidgetLibrary()
    ];

    $build['footer_bottom']['context_selector'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="block-place-context-selector-wrapper">',
      '#suffix' => '</div>',
      'selector' => [
        '#type' => 'select',
        '#default_value' => $context,
        '#options' => $contexts,
        '#title' => $this->t('Select Context'),
        '#attributes' => [
          'id' => 'block-place-context-selector'
        ]
      ],
      '#attached' => [
        'library' => [
          'os_widgets/layout'
        ],
        'drupalSettings' => [
          'layoutContexts' => $contexts,
        ]
      ]
    ];

    $build['footer_bottom']['actions'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="block-place-actions-wrapper">',
      '#suffix' => '</div>',
      'save' => [
        '#type' => 'button',
        '#value' => $this->t('Save')
      ],
      'reset' => [
        '#type' => 'button',
        '#value' => $this->t('Reset')
      ]
    ];

    return $build;
  }

  private function buildWidgetLibrary() {

    /** @var BlockRepositoryInterface $blockRepository */
    $blockRepository = \Drupal::service('os_widgets.block.repository');
    $allBlocks = $blockRepository->getVisibleBlocksPerRegion();

    /** @var Block[] $blocks */
    $blocks = $allBlocks[0];

    /** @var BlockContentType[] $block_types */
    $block_types = $this->entityTypeManager->getStorage('block_content_type')->loadMultiple();
    $factory_links = [];
    $t = [];
    foreach ($block_types as $bt) {
      $t[] = $bt->id();
      $factory_links[$bt->id()] = [
        'title' => $bt->label(),
        'url' => Url::fromRoute('block_content.add_form', ['block_content_type' => $bt->id()]),
        'attributes' => [
          'title' => $bt->label(),
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 800,
            'autoOpen' => true,
          ]),
        ]
      ];
    }

    $output = [
      'factory' => [
        '#type' => 'button',
        '#title' => $this->t('Create New Widget'),
        '#attributes' => [
          'id' => 'create-new-widget-btn'
        ]
      ],
      'filter' => [
        '#type' => 'textfield',
        '#title' => $this->t('Filter blocks'),
        '#maxlength' => 60,
        '#size' => 60
      ],
      'existing-blocks' => [
        '#prefix' => '<div id="block-list">',
        '#suffix' => '</div>',
      ],
      'factories' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'factory-wrapper',
        ],
        'title' => [
          '#markup' => '<h3>'.$this->t('Select Widget Type').'</h3>',
        ],
        'close' => [
          '#markup' => '<div class="close">X</div>'
        ],
        'links' => [
          '#theme' => 'links',
          '#links' => $factory_links
        ]
      ]
    ];

    foreach ($blocks as $b) {
      $block_build = [
        '#type' => 'inline_template',
        '#template' => '<div class="block" data-block-id="{{ id }}"><h3 class="block-title">{{ title }}</h3>{{ content }}</div>',
        '#context' => [
          'id' => $b->id(),
          'title' => '',
          'content' => ''
        ]
      ];
      $block_build['#context']['title'] = $b->label();
      $block_build['#context']['content'] = $this->entityTypeManager->getViewBuilder('block')->view($b);
      $output['existing-blocks'][$b->id()] = $block_build;
    }

    return $output;
  }

}