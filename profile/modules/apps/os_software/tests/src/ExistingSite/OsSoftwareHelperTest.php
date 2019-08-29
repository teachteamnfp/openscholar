<?php

namespace Drupal\Tests\os_software\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class OsSoftwareHelperTest.
 *
 * @group os
 * @group kernel
 *
 * @package Drupal\Tests\os_software\ExistingSite
 */
class OsSoftwareHelperTest extends OsExistingSiteTestBase {

  /**
   * Os Software Helper.
   *
   * @var \Drupal\os_software\OsSoftwareHelperInterface
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->helper = $this->container->get('os.software.helper');
  }

  /**
   * Test release title update logic.
   */
  public function testPrepareReleaseTitle() {
    $project = $this->createNode([
      'type' => 'software_project',
    ]);
    $release = $this->createNode([
      'type' => 'software_release',
      'field_software_project' => [
        $project->id(),
      ],
      'field_software_version' => 'v1.1.2',
    ]);
    $title = $this->helper->prepareReleaseTitle($release);
    $this->assertEquals($project->label() . ' v1.1.2', $title);
  }

  /**
   * Test release title default project label.
   */
  public function testPrepareReleaseDefaultProjectTitle() {
    $release = $this->createNode([
      'type' => 'software_release',
      'field_software_version' => 'v1.1.2',
    ]);
    $title = $this->helper->prepareReleaseTitle($release);
    $this->assertEquals('Project Release v1.1.2', $title);
  }

}
