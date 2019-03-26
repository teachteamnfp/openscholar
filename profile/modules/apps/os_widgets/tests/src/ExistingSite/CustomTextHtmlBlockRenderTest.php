<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\os_widgets\Plugin\OsWidgets\CustomTextHtmlWidget;

/**
 * Class CustomTextHtmlBlockRenderTest.
 *
 * @group kernel
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\CustomTextHtmlWidget
 */
class CustomTextHtmlBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\CustomTextHtmlWidget
   */
  protected $customTextHtmlWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->customTextHtmlWidget = new CustomTextHtmlWidget([], '', [], $this->entityTypeManager);
  }

  /**
   * Test build with suspicious/unsecure body.
   */
  public function testBuildSuspiciousBody() {

    $block_content = $this->createBlockContent([
      'type' => 'custom_text_html',
      'body' => [
        'Lorem<script type="application/javascript">var bad_code;</script> Ipsum',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<p>Loremvar bad_code; Ipsum</p>', $markup->__toString());
  }

  /**
   * Test special chars in css classes field.
   */
  public function testBuildClassSpecialChars() {

    $block_content = $this->createBlockContent([
      'type' => 'custom_text_html',
      'field_css_classes' => [
        'text-_\'"+!%/=$ß¤×÷;css second-class  third-with-extra-space',
      ],
    ]);
    $build = [];
    $this->customTextHtmlWidget->buildBlock($build, $block_content);
    $this->assertSame('text---ß¤×÷css', $build['#extra_classes'][0]);
    $this->assertSame('second-class', $build['#extra_classes'][1]);
    $this->assertSame('third-with-extra-space', $build['#extra_classes'][2]);
  }

}
