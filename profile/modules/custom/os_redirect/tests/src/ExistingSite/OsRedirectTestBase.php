<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test base for redirect tests.
 */
class OsRedirectTestBase extends OsExistingSiteTestBase {

  /**
   * Creates a redirect.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\redirect\Entity\Redirect
   *   The created redirect entity.
   */
  protected function createRedirect(array $values = []) : EntityInterface {
    $redirect = $this->container->get('entity_type.manager')->getStorage('redirect')->create($values + [
      'type' => 'redirect',
    ]);
    $redirect->enforceIsNew();
    $redirect->save();

    $this->markEntityForCleanup($redirect);

    return $redirect;
  }

}
