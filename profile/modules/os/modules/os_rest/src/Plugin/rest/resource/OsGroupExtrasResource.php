<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\purl\Plugin\ModifierIndex;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class OsGroupExtrasResource.
 *
 * Validate fields.
 *
 * @RestResource(
 *   id = "group:extras",
 *   label = @Translation("Group Extras"),
 *   uri_paths = {
 *     "canonical" = "/api/group/validate/{field}/{value}"
 *   }
 * )
 */
class OsGroupExtrasResource extends ResourceBase {

  /**
   * PURL Modifier Index.
   *
   * @var ModifierIndex
   */
  protected $modifierIndex;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('purl.modifier_index'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, ModifierIndex $modifierIndex, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->modifierIndex = $modifierIndex;
  }

  /**
   * Handler for get method.
   *
   * @param string $field
   *    The field to validate.
   * @param $value
   *    The value of the field.
   *
   * @return ResourceResponse
   *    The response to return to the client.
   */
  public function get($field, $value) {
    $output = [];
    switch ($field) {
      case 'url':
        $output = $this->testPurl($value);
        break;
    }

    return new ResourceResponse($output);
  }

  /**
   * Validate the supplied purl.
   *
   * @param string $value
   *    The value to test.
   *
   * @return array
   *    Array of errors.
   */
  protected function testPurl($value) {
    // Checking site creation permission
    $return = array();
    
    // access check.
    //  $return['msg'] = "Not-Permissible";
    //  return $return;
    
    //Validate new vsite URL
    $return['msg'] = '';

    if (strlen($value) < 3 || !preg_match('!^[a-z0-9-]+$!', $value)) {
      $return['msg'] = 'Invalid';
    }
    elseif ($this->modifierExists($value)) {
      $return['msg'] = "Not-Available";
    }
    else {
      $return['msg'] = "Available";
    }
    return $return;

  }

  protected function modifierExists($value) {
    $modifiers = $this->modifierIndex->findAll();

    foreach ($modifiers as $m) {
      if ($m->getModifierKey() == $value) {
        return true;
      }
    }

    return false;
  }

}
