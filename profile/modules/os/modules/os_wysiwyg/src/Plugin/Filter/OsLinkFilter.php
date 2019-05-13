<?php

namespace Drupal\os_wysiwyg\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use InvalidArgumentException;
use Drupal\file\Entity\File;

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
class OsLinkFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    if (stristr($text, 'data-fid') !== FALSE) {
      foreach ($xpath->query('//a[@data-fid]') as $node) {
        /** @var \DOMElement $node */
        $fid = $node->getAttribute('data-fid');
        $node->removeAttribute('data-fid');
        $file = File::load($fid);
        if (!empty($file)) {
          $uri = $file->getFileUri();
          $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
          $file_path = $stream_wrapper_manager->getExternalUrl();
          $node->setAttribute('href', $file_path);
        }
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
