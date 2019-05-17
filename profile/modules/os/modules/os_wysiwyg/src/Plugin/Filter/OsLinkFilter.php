<?php

namespace Drupal\os_wysiwyg\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\os_wysiwyg\OsLinkHelperInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to display link based on data attributes.
 *
 * @Filter(
 *   id = "os_link_filter",
 *   title = @Translation("Convert File links to correct path"),
 *   description = @Translation("This filter will convert the paths of links to files to ensure they're always correct."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class OsLinkFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Os Link Helper.
   *
   * @var \Drupal\os_wysiwyg\OsLinkHelperInterface
   */
  protected $osLinkHelper;

  /**
   * OsLinkFilter constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\os_wysiwyg\OsLinkHelperInterface $os_link_helper
   *   Os Link Helper.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, OsLinkHelperInterface $os_link_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->osLinkHelper = $os_link_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('os_wysiwyg.os_link_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    if (stristr($text, 'data-mid') !== FALSE) {
      foreach ($xpath->query('//a[@data-mid]') as $node) {
        /** @var \DOMElement $node */
        $mid = $node->getAttribute('data-mid');
        $node->removeAttribute('data-mid');
        $node->setAttribute('href', $this->osLinkHelper->getFileUrlFromMedia($mid));
      }
    }
    if (stristr($text, 'data-url') !== FALSE) {
      foreach ($xpath->query('//a[@data-url]') as $node) {
        /** @var \DOMElement $node */
        $data_url = $node->getAttribute('data-url');
        $node->removeAttribute('data-url');
        try {
          $url = Url::fromUserInput($data_url);
        }
        catch (InvalidArgumentException $e) {
          // External url given.
          $url = Url::fromUri($data_url);
        }
        $node->setAttribute('href', $url->toString());
      }
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

}
