<?php

namespace Drupal\os_rest\Normalizer;


use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

class OsGroupNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\group\Entity\GroupInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $temp = parent::normalize($entity, $format, $context);

    return $temp;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    // Take the data we get and massage it into a form Drupal understands.
    $entityData = [
      'type' => [['target_id' => $data['type']]],
      'uid' => [['target_id' => $data['owner']]],
      'label' => [['value' => $data['label']]],
      'field_privacy_level' => [['value' => $data['privacy']]],
      'field_preset' => [['target_id' => $data['preset']]],
      'path' => [['alias' => '/'.$data['purl'], 'langcode' => 'en']]
    ];

    if (isset($data['parent'])) {
      $entityData['parent'] = [['target_id' => $data['parent']]];
    }

    $entity = parent::denormalize($entityData, $class, $format, $context);

    // This entity hasn't been saved yet and won't be until later.
    // We need to stuff any relevant data into the entity and process it after it's been saved.
    $entity->_data_extra['theme'] = $data['theme'];

    return $entity;
  }

}
