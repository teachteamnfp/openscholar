<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\repec\Series\wpaper\Template;

/**
 * Class RepecTemplateFactoryTest.
 *
 * @group kernel
 * @group publications
 */
class RepecTemplateFactoryTest extends TestBase {

  /**
   * Template factory.
   *
   * @var \Drupal\repec\TemplateFactory
   */
  protected $templateFactory;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Test reference.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->templateFactory = $this->container->get('template_factory');
    $this->configFactory = $this->container->get('config.factory');
    $this->reference = $this->createReference();
  }

  /**
   * Tests whether correct template class is created from template factory.
   *
   * @covers \Drupal\repec\TemplateFactory
   * @covers \Drupal\repec\Series\Base
   */
  public function testTemplateClass() {
    /** @var \Drupal\Core\Config\ImmutableConfig $repec_settings */
    $repec_settings = $this->configFactory->get('repec.settings');
    /** @var array|null $bundle_settings */
    $bundle_settings = unserialize($repec_settings->get("repec_bundle.{$this->reference->getEntityTypeId()}.{$this->reference->bundle()}"));

    $template_class = $this->templateFactory->create($bundle_settings['serie_type'], $this->reference);

    $this->assertInstanceOf(Template::class, $template_class);
  }

}
