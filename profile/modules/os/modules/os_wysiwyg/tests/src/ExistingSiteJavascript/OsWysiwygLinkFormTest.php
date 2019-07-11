<?php

namespace Drupal\os_wysiwyg\ExistingSiteJavascript;


use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

class OsWysiwygLinkFormTest extends OsExistingSiteJavascriptTestBase {

  protected $user;

  public function setUp() {
    parent::setUp();
    $this->user = $this->coreCreateUser();
    $this->group->addMember($this->user, [
      'group_roles' => [
        'personal-administrator'
      ]
    ]);
  }

  public function testLinkForm() {
    error_log('login');
    $this->drupalLogin($this->user);
error_log('visit');
    $this->drupalGet($this->groupAlias . '/node/add/page');

    error_log('open form');
    $page = $this->getSession()->getPage();
    $button = $page->find('css', '.cke_button__oslink');
    $button->click();
    error_log('wait for element');
    $this->assertSession()->waitForElement('css', '#owl-field-text');
    error_log('element open');

    $dialog = $page->find('css', '.wysiwyg-link-tool-wrapper');
    $dialog->fillField('Link Text', 'Internal URL');
    $dialog->fillField('Title Text', 'Internal Title');
    $dialog->fillfield('Website URL', 'about');
    $dialog->checkField('Open this link in a new tab');
    $dialog->findButton('Insert')->click();

    $this->assertWysiwygContains('edit-body-0-value', '<a data-cke-saved-href="about" href="about" data-url="about" target="_blank" title="Internal Title">Internal URL</a>');

  }

  protected function assertWysiwygContains($wysiwyg, $content) {
    $return = $this->getSession()->evaluateScript('CKEDITOR.instances['.$wysiwyg.'].getData()');
    $this->assertContains($content, $return, "Content not found, or wysiwyg with that id does not exist.");
  }
}
