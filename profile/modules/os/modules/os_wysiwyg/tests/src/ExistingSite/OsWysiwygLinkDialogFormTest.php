<?php

namespace Drupal\Tests\os_wysiwyg\ExistingSite;

use Drupal\Core\Form\FormState;
use Drupal\os_wysiwyg\Form\OsWysiwygLinkDialog;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;

/**
 * Class OsWysiwygLinkDialogFormTest.
 *
 * @package Drupal\Tests\os_wysiwyg\ExistingSite
 * @group kernel
 * @group wysiwyg
 */
class OsWysiwygLinkDialogFormTest extends OsExistingSiteTestBase {

  use ExistingSiteTestTrait;

  /**
   * Test form render and simple submit.
   */
  public function testFormRender() {
    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = $this->container->get('form_builder');

    /** @var \Drupal\os_wysiwyg\OsLinkHelperInterface $os_link_helper */
    $os_link_helper = $this->container->get('os_wysiwyg.os_link_helper');

    $form = new OsWysiwygLinkDialog($os_link_helper);
    $form_state = (new FormState())
      ->setValues([
        'attributes' => [
          'text' => 'test123',
        ],
      ]);
    $form_builder->submitForm($form, $form_state);

    $this->assertEquals(count($form_state->getErrors()), 0);
  }

}
