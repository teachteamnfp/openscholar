<?php

namespace Drupal\os_rest\Normalizer;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Image\Image;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Converts Media to more usable format
 * @package Drupal\os_rest\Normalizer
 */
class OsMediaNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\media\MediaInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = null, array $context = array ()) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $object; // just to get type hinting
    $temp = parent::normalize($object, $format, $context);

    $output['mid'] = $media->id();
    $output['name'] = $media->getName();
    $output['created'] = $media->getCreatedTime();
    $output['changed'] = $temp['changed'][0]['value'];
    $output['type'] = $media->bundle();
    $source = $media->getSource();
    $file = $source->getSourceFieldValue($media);
    if (is_numeric($file)) {
      /** @var FileInterface $file */
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file);
      $output['url'] = file_create_url($file->getFileUri());
      $output['size'] = $file->getSize();
      $output['filename'] = $file->getFilename();

      if (!empty($temp['field_media_image'])) {
        $output['alt'] = $temp['field_media_image'][0]['alt'];
        $output['title'] = $temp['field_media_image'][0]['title'];
      }
    }
    else {
      $output['filename'] = $file;
    }
    $output['description'] = !empty($temp['field_description']) ? $temp['field_description'][0]['value'] : '';
    $output['thumbnail'] = $temp['thumbnail'][0]['url'];
    $output['reference'] = $temp;

    return $output;
  }

  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $entity = parent::denormalize($data, $class, $format, $context);

    if (in_array('alt', $entity->_restSubmittedFields) || in_array('title', $entity->_restSubmittedFields)) {
      $entity->_restSubmittedFields = array_diff($entity->_restSubmittedFields, ['alt', 'title']);
      $entity->_restSubmittedFields[] = 'field_media_image';
    }
    $entity->restSubmittedFields = array_diff($entity->_restSubmittedFields, ['fid']);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalizeFieldData(array $data, FieldableEntityInterface $entity, $format, array $context) {
    $original = \Drupal::routeMatch()->getParameter('media');

    if (!empty($data['name'])) {
      $input = [
        'name' => [
          'value' => $data['name']
        ]
      ];
      $fieldList = $entity->get('name');
      $fieldList->setValue([]);
      $class = get_class($fieldList);
      $context['target_instance'] = $fieldList;
      $this->serializer->deserialize(json_encode($input), $class, $format, $context);
    }
    if (!empty($data['changed'])) {
      $input = [
        'changed' => [
          'value' => $data['changed']
        ]
      ];
      $fieldList = $entity->get('changed');
      $fieldList->setValue([]);
      $class = get_class($fieldList);
      $context['target_instance'] = $fieldList;
      $this->serializer->deserialize(json_encode($input), $class, $format, $context);
    }
    if (!empty($data['alt']) || !empty($data['title'])) {
      $input = $original->get('field_media_image')->get(0)->getValue();

      if (!empty($data['alt'])) {
        $input['alt'] = $data['alt'];
      }
      if (!empty($data['title'])) {
        $input['title'] = $data['title'];
      }

      $fieldList = $entity->get('field_media_image');
      $class = get_class($fieldList);
      $context['target_instance'] = $fieldList;
      $this->serializer->deserialize(json_encode([$input]), $class, $format, $context);
    }
    if (!empty($data['description'])) {
      $input = [
        'field_description' => $data['description']
      ];

      $fieldList = $entity->get('name');
      $fieldList->setValue([]);
      $class = get_class($fieldList);
      $context['target_instance'] = $fieldList;
      $this->serializer->deserialize($input, $class, $format, $context);
    }
  }

}
