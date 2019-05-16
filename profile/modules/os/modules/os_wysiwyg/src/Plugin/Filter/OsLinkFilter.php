<?php

namespace Drupal\os_wysiwyg\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\media\Entity\Media;
use InvalidArgumentException;

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

    if (stristr($text, 'data-mid') !== FALSE) {
      foreach ($xpath->query('//a[@data-mid]') as $node) {
        /** @var \DOMElement $node */
        $mid = $node->getAttribute('data-mid');
        $node->removeAttribute('data-mid');
        $node->setAttribute('href', $this->getFileUrlFromMedia($mid));
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

  /**
   * Get file url from media id.
   *
   * @param int $mid
   *   Media ID.
   *
   * @return string
   *   File url.
   */
  protected function getFileUrlFromMedia(int $mid) {
    $url = '';
    $media = Media::load($mid);
    if (empty($media)) {
      return $url;
    }
    $file = $this->getFileFromMedia($media);
    if (empty($file)) {
      return $url;
    }
    $uri = $file->getFileUri();
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
    $url = $stream_wrapper_manager->getExternalUrl();
    return $url;
  }

  /**
   * Get file from media.
   *
   * @param \Drupal\media\Entity\Media $media
   *   Media entity.
   *
   * @return \Drupal\file\Entity\File|null
   *   File entity or null.
   */
  protected function getFileFromMedia(Media $media) {
    $file_field_name = '';
    if ($media->hasField('field_media_file')) {
      $file_field_name = 'field_media_file';
    }
    if ($media->hasField('field_media_image')) {
      $file_field_name = 'field_media_image';
    }
    if (empty($file_field_name)) {
      return NULL;
    }
    $referencedEntities = $media->get($file_field_name)->referencedEntities();
    return array_shift($referencedEntities);
  }

}
