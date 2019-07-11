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
    $this->drupalLogin($this->user);
    $this->drupalGet($this->getAbsoluteUrl($this->groupAlias . '/node/add/page'));

    $this->assertSession()->waitForElement('css', '.cke_button__oslink');

    $dialog = $this->getLinkForm();
    $dialog->fillField('Link Text', 'Internal URL');
    $dialog->fillField('Title Text', 'Internal Title');
    $dialog->fillfield('Website URL', 'about');
    $dialog->checkField('Open this link in a new tab');
    $dialog->findButton('Insert')->click();

    $this->assertWysiwygContains('edit-body-0-value', '<a data-url="about" href="about" target="_blank" title="Internal Title">Internal URL</a>');
  }

  protected function assertWysiwygContains($wysiwyg, $content) {
    $return = $this->getSession()->evaluateScript('CKEDITOR.instances["'.$wysiwyg.'"].getData()');
    $this->assertContains($content, $return, "Content not found, or wysiwyg with that id does not exist.");
  }

  protected function getLinkForm() {
    $button = $this->getSession()->getPage()->find('css', '.cke_button__oslink');
    $button->click();
    $this->assertSession()->waitForElement('css', '#owl-field-text');
    return $this->getSession()->getPage()->find('css', '.wysiwyg-link-tool-wrapper');
  }

}
