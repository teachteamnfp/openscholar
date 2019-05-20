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

  protected $form;
  /**
   * Form Builder Interface.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface*/
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\os_wysiwyg\OsLinkHelperInterface $os_link_helper */
    $os_link_helper = $this->container->get('os_wysiwyg.os_link_helper');

    $this->form = new OsWysiwygLinkDialog($os_link_helper);
    $this->formBuilder = $this->container->get('form_builder');
  }

  /**
   * Test form render and simple submit.
   */
  public function testFormRender() {

    $form_state = (new FormState())
      ->setValues([
        'attributes' => [
          'text' => 'test123',
        ],
      ]);
    $this->formBuilder->submitForm($this->form, $form_state);

    $this->assertEquals(count($form_state->getErrors()), 0);

  }

  /**
   * Test form submit ajax handler response command.
   */
  public function testFormSubmitAjax() {
    $form_state = (new FormState())
      ->setValues([
        'attributes' => [
          'text' => 'test123',
        ],
        'href' => '/example',
        'email' => 'example@test.com',
        'link_to__active_tab' => 'edit-website',
        'target_option' => '1',
        'entity_browser_select' => [
          'media:10' => 'media:10',
        ],
      ]);
    // Manually call ajax submit handler.
    $form_array = $this->formBuilder->getForm(OsWysiwygLinkDialog::class);
    /** @var \Drupal\Core\Ajax\AjaxResponse $response */
    $response = $this->form->submitDialogForm($form_array, $form_state);
    $commands = $response->getCommands();
    $this->assertNotEmpty($commands, 'Response commands is empty.');
    $first_command_values = $commands[0]['values'];
    $this->assertEquals('test123', $first_command_values['attributes']['text']);
    $this->assertEquals('/example', $first_command_values['href']);
    $this->assertEquals('example@test.com', $first_command_values['email']);
    $this->assertEquals('10', $first_command_values['selectedMedia']);
    $this->assertEquals('', $first_command_values['selectedMediaUrl']);
    $this->assertEquals('edit-website', $first_command_values['activeTab']);
    $this->assertEquals('1', $first_command_values['targetOption']);
  }

}
