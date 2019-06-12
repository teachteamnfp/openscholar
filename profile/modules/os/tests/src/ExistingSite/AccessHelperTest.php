<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * AccessHelperTest.
 *
 * @group kernel
 * @group os
 * @coversDefaultClass \Drupal\os\AccessHelper
 */
class AccessHelperTest extends OsExistingSiteTestBase {

  /**
   * @covers ::checkCreateAccess
   */
  public function testCheckCreateAccess(): void {
    // Setup.
    /** @var \Drupal\os\AccessHelperInterface $access_helper */
    $access_helper = $this->container->get('os.access_helper');
    $account = $this->createUser();

    // Negative tests.
    $this->assertInstanceOf(AccessResultNeutral::class, $access_helper->checkCreateAccess($account, 'not_relevant'));

    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    $vsite_context_manager->activateVsite($this->group);
    $this->assertInstanceOf(AccessResultNeutral::class, $access_helper->checkCreateAccess($account, 'non_existing_group_plugin'));

    $this->assertInstanceOf(AccessResultNeutral::class, $access_helper->checkCreateAccess($account, 'group_node:class'));

    // Positive tests.
    $this->group->addMember($account);
    $this->assertInstanceOf(AccessResultAllowed::class, $access_helper->checkCreateAccess($account, 'group_node:class'));
  }

  /**
   * Tests access for node entities update.
   *
   * @covers ::checkAccess
   */
  public function testCheckAccessNodeUpdate(): void {
    $node = $this->createNode();
    $account = $this->createUser();

    $this->assertCheckAccess($node, 'update', $account);
  }

  /**
   * Tests access for node entities delete.
   *
   * @covers ::checkAccess
   */
  public function testCheckAccessNodeDelete(): void {
    $node = $this->createNode();
    $account = $this->createUser();

    $this->assertCheckAccess($node, 'delete', $account);
  }

  /**
   * Tests access for non-node entities update.
   *
   * @covers ::checkAccess
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCheckAccessEntityUpdate(): void {
    $reference = $this->createReference();
    $account = $this->createUser();

    $this->assertCheckAccess($reference, 'update', $account);
  }

  /**
   * Tests access for non-node entities delete.
   *
   * @covers ::checkAccess
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCheckAccessEntityDelete(): void {
    $reference = $this->createReference();
    $account = $this->createUser();

    $this->assertCheckAccess($reference, 'delete', $account);
  }

  /**
   * Helper access check assertion method.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check access to.
   * @param string $operation
   *   The operation to be performed on the entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user trying to access the entity.
   */
  protected function assertCheckAccess(EntityInterface $entity, string $operation, AccountInterface $account): void {
    /** @var \Drupal\os\AccessHelperInterface $access_helper */
    $access_helper = $this->container->get('os.access_helper');

    // Negative tests.
    $this->assertInstanceOf(AccessResultNeutral::class, $access_helper->checkAccess($entity, $operation, $account));

    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    $vsite_context_manager->activateVsite($this->group);

    $this->assertInstanceOf(AccessResultNeutral::class, $access_helper->checkAccess($entity, $operation, $account));

    // Positive tests.
    $this->group->addMember($account);
    $entity->setOwner($account)->save();
    $this->assertInstanceOf(AccessResultAllowed::class, $access_helper->checkAccess($entity, $operation, $account));
    $this->addGroupAdmin($account, $this->group);
    $this->assertInstanceOf(AccessResultAllowed::class, $access_helper->checkAccess($entity, $operation, $account));
  }

}
