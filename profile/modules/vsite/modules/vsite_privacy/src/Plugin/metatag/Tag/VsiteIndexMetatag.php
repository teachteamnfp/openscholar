<?php

namespace Drupal\vsite_privacy\Plugin\metatag\Tag;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Vsite visibility robots meta tag.
 *
 * @MetatagTag(
 *   id = "vsite_privacy_robots",
 *   label = @Translation("Enable checking vsite visibility to robots"),
 *   description = @Translation("Place a noindex meta tag if site privacy is unindexed."),
 *   name = "robots",
 *   group = "basic",
 *   weight = 1,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class VsiteIndexMetatag extends MetaNameBase implements ContainerFactoryPluginInterface {

  private $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.context_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $element = []) {
    $form = [
      '#type' => 'checkbox',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#default_value' => $this->value,
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function output() {
    $element = [];
    if (empty($this->value)) {
      return $element;
    }
    $group = $this->vsiteContextManager->getActiveVsite();
    if (empty($group)) {
      return $element;
    }
    $privacy_level = $group->get('field_privacy_level')->getValue();
    if ($privacy_level[0]['value'] != 'unindexed') {
      return $element;
    }
    $element = parent::output();
    $element['#attributes']['content'] = 'noindex';

    return $element;
  }

}
