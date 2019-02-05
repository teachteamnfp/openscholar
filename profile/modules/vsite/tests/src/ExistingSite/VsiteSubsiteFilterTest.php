<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\views\Views;

/**
 * Test the Subsite Vsite Views Filter.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\Plugin\views\filter\VsiteSubsiteFilter
 */
class VsiteSubsiteFilterTest extends VsiteExistingSiteTestBase {

  /**
   * Group dummy content is being assigned (or not) to.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  /**
   * Group dummy content is being assigned (or not) to.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $groupOther;

  /**
   * Group hidden.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $groupHidden;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    $this->group = $this->createGroup([
      'type' => 'personal',
      'label' => 'Site01',
      'field_privacy_level' => 'public',
    ]);
    $this->groupOther = $this->createGroup([
      'type' => 'personal',
      'label' => 'OtherSite',
      'field_privacy_level' => 'public',
    ]);
    $this->groupHidden = $this->createGroup([
      'type' => 'subsite_test',
      'label' => 'HiddenSite',
      'field_parent_site' => $this->group->id(),
      'field_privacy_level' => 'private',
    ]);

    $this->createGroup([
      'type' => 'subsite_test',
      'label' => 'SubSite01',
      'field_parent_site' => $this->group->id(),
      'field_privacy_level' => 'public',
    ]);
    $this->createGroup([
      'type' => 'subsite_test',
      'label' => 'SubSite02',
      'field_parent_site' => $this->group->id(),
      'field_privacy_level' => 'public',
    ]);

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Retrieves the results for this test's view.
   *
   * @return \Drupal\views\ResultRow[]
   *   A list of view results.
   */
  protected function getViewResults() {
    $view = Views::getView('os_subsites');
    $view->setDisplay('page_1');

    if ($view->preview()) {
      $names = [];
      foreach ($view->result as $r) {
        $names[] = $r->_entity->label();
      }
      return $names;
    }

    return [];
  }

  /**
   * Check that only the subsite group shows up in a subsites list.
   */
  public function testInsideOfVsite() {
    $this->vsiteContextManager->activateVsite($this->group);

    $results = $this->getViewResults();

    $this->assertContains('SubSite01', $results);
    $this->assertContains('SubSite02', $results);
    $this->assertNotContains('HiddenSite', $results);
  }

  /**
   * Check that only the subsite group not shows up in other subsites list.
   */
  public function testOtherVsite() {
    $this->vsiteContextManager->activateVsite($this->groupOther);

    $results = $this->getViewResults();

    $this->assertNotContains('SubSite01', $results);
    $this->assertNotContains('SubSite02', $results);
    $this->assertNotContains('HiddenSite', $results);
  }

}
