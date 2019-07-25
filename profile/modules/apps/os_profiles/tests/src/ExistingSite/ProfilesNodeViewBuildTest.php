<?php

namespace Drupal\Tests\os_profiles\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_profiles module node view.
 *
 * @group kernel
 * @group profiles
 */
class ProfilesNodeViewBuildTest extends OsExistingSiteTestBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;
  protected $personNode;
  protected $renderer;
  protected $viewBuilder;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = $this->container->get('vsite.context_manager');
    $vsiteContextManager->activateVsite($this->group);
    $this->config = $this->container->get('config.factory');
    $this->renderer = $this->container->get('renderer');
    $this->personNode = $this->createNode([
      'type' => 'person',
      'field_first_name' => $this->randomMachineName(),
      'field_last_name' => $this->randomMachineName(),
    ]);
    $this->group->addContent($this->personNode, 'group_node:person');
    $entity_type = 'node';
    $this->viewBuilder = $this->container->get('entity_type.manager')->getViewBuilder($entity_type);
  }

  /**
   * Test node view listing default image.
   */
  public function testNodeViewTeaserDefaultImage() {
    $markup = $this->renderNode('teaser');
    $this->assertContains('person-default-image.png', $markup->__toString());

    $markup = $this->renderNode('sidebar_teaser');
    $this->assertContains('person-default-image.png', $markup->__toString());
  }

  /**
   * Test node view full default image.
   */
  public function testNodeViewFullDefaultImage() {
    $markup = $this->renderNode('full');
    $this->assertContains('person-default-image-big.png', $markup->__toString());
  }

  /**
   * Test node view listing custom default image.
   */
  public function testNodeViewTeaserCustomDefaultImage() {
    $file = $this->createFile('image');
    $profiles_config = $this->config->getEditable('os_profiles.settings');
    $profiles_config->set('default_image_fid', $file->id());
    $profiles_config->save();

    $markup = $this->renderNode('teaser');
    $this->assertContains('styles/crop_photo_person/public/image-test.png', $markup->__toString());
    $this->assertContains('width="75" height="75"', $markup->__toString());
    $markup = $this->renderNode('sidebar_teaser');
    $this->assertContains('styles/crop_photo_person/public/image-test.png', $markup->__toString());
    $this->assertContains('width="75" height="75"', $markup->__toString());
  }

  /**
   * Test node view full custom default image.
   */
  public function testNodeViewFullCustomDefaultImage() {
    $file = $this->createFile('image');
    $profiles_config = $this->config->getEditable('os_profiles.settings');
    $profiles_config->set('default_image_fid', $file->id());
    $profiles_config->save();

    $markup = $this->renderNode('full');
    $this->assertContains('styles/crop_photo_person_full/public/image-test.png', $markup->__toString());
    $this->assertContains('width="180" height="220"', $markup->__toString());
  }

  /**
   * Test node view listing person image.
   */
  public function testNodeViewTeaserPersonImage() {
    $file = $this->createFile('image');
    $personNodeWithImage = $this->createNode([
      'type' => 'person',
      'field_first_name' => $this->randomMachineName(),
      'field_last_name' => $this->randomMachineName(),
      'field_photo_person' => [
        'target_id' => $file->id(),
      ],
    ]);

    $build = $this->viewBuilder->view($personNodeWithImage, 'teaser');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($build);
    $this->assertContains('styles/crop_photo_person/public/image-test.png', $markup->__toString());
    $this->assertContains('width="75" height="75"', $markup->__toString());

    $build = $this->viewBuilder->view($personNodeWithImage, 'sidebar_teaser');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($build);
    $this->assertContains('styles/crop_photo_person/public/image-test.png', $markup->__toString());
    $this->assertContains('width="75" height="75"', $markup->__toString());
  }

  /**
   * Test node view full person image.
   */
  public function testNodeViewFullPersonImage() {
    $file = $this->createFile('image');
    $personNodeWithImage = $this->createNode([
      'type' => 'person',
      'field_first_name' => $this->randomMachineName(),
      'field_last_name' => $this->randomMachineName(),
      'field_photo_person' => [
        'target_id' => $file->id(),
      ],
    ]);

    $build = $this->viewBuilder->view($personNodeWithImage, 'full');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($build);
    $this->assertContains('styles/crop_photo_person_full/public/image-test.png', $markup->__toString());
    $this->assertContains('width="180" height="220"', $markup->__toString());
  }

  /**
   * Render person node.
   *
   * @param string $view_mode
   *   View mode.
   *
   * @return mixed
   *   Rendered markup.
   */
  protected function renderNode(string $view_mode) {
    $build = $this->viewBuilder->view($this->personNode, $view_mode);
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($build);
    return $markup;
  }

}
