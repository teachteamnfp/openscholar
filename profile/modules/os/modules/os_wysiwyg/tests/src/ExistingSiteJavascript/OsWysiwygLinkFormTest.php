<?php

namespace Drupal\os_wysiwyg\ExistingSiteJavascript;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests the angular Link Form.
 *
 * @group functional-javascript
 * @group os
 */
class OsWysiwygLinkFormTest extends OsExistingSiteJavascriptTestBase {

  /**
   * The user we're logging in as.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->coreCreateUser();
    $this->group->addMember($this->user, [
      'group_roles' => [
        'personal-administrator',
      ],
    ]);

    /** @var \Drupal\Core\File\FileSystemInterface $filesystem */
    $filesystem = \Drupal::service('file_system');
    $file = File::create([
      'uri' => drupal_get_path('module', 'os_wysiwyg') . '/tests/files/test.txt',
    ]);
    $file->setPermanent();
    $file->save();
    $this->markEntityForCleanUp($file);

    $media = Media::create([
      'bundle' => 'document',
      'field_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media->setPublished(TRUE);
    $media->save();
    $this->markEntityForCleanUp($media);

    $this->group->addContent($media, 'group_entity:media');
  }

  /**
   * Tests all workflows of the link form.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testLinkForm() {
    $this->drupalLogin($this->user);
    $this->drupalGet($this->getAbsoluteUrl($this->groupAlias . '/node/add/page'));

    $this->assertSession()->waitForElement('css', '.cke_button__oslink');

    $dialog = $this->getLinkForm();
    $dialog->fillField('Link Text', 'Internal URL');
    $dialog->fillField('Title Text', 'Internal Title');
    $dialog->fillField('Website URL', 'about');
    $dialog->checkField('Open this link in a new tab');
    $dialog->findButton('Insert')->click();

    $wys_id = 'edit-body-0-value';
    $this->assertWysiwygContains($wys_id, '<a data-url="about" href="about" target="_blank" title="Internal Title">Internal URL</a>');
    $this->clearWysiwyg($wys_id);

    $dialog = $this->getLinkForm();
    $this->assertEmpty($dialog->findField('Link Text')->getValue(), 'Reusing an existing link, should be a new link.');
    $dialog->fillField('Link Text', 'External URL');
    $dialog->fillField('Title Text', 'External Title');
    $dialog->fillField('Website URL', 'http://www.google.com');
    $dialog->findButton('Insert')->click();

    $this->assertWysiwygContains($wys_id, '<a data-url="http://www.google.com" href="http://www.google.com" title="External Title">External URL</a>');
    $this->clearWysiwyg($wys_id);

    $dialog = $this->getLinkForm();
    $this->assertEmpty($dialog->findField('Link Text')->getValue(), 'Reusing an existing link, should be a new link.');
    $dialog->fillField('Link Text', 'Email');
    $dialog->fillField('Title Text', 'Email Title');
    $dialog->find('xpath', "//div[contains(@class, 'owl-tab-button') and contains(., 'E-mail')]")->click();
    $dialog->fillField('E-mail', 'test@theopenscholar.com');
    $dialog->findButton('Insert')->click();

    $this->assertWysiwygContains($wys_id, '<a href="mailto:test@theopenscholar.com" title="Email Title">Email</a>');
  }

  /**
   * Assert that the wysiwyg with the given id contains the given content.
   *
   * @param string $wysiwyg
   *   ID of the wysiwyg to search in.
   * @param string $content
   *   The string to search for.
   */
  protected function assertWysiwygContains($wysiwyg, $content) {
    $return = $this->getSession()->evaluateScript('CKEDITOR.instances["' . $wysiwyg . '"].getData()');
    $this->assertContains($content, $return, "Content not found, or wysiwyg with that id does not exist.");
  }

  /**
   * Empties the wysiwyg of all content.
   *
   * @param string $wysiwyg
   *   ID of the wysiwyg to clear.
   */
  protected function clearWysiwyg($wysiwyg) {
    $this->getSession()->executeScript('CKEDITOR.instances["' . $wysiwyg . '"].setData("")');
  }

  /**
   * Opens the link form.
   *
   * @return \Behat\Mink\Element\NodeElement|mixed|null
   *   The element containing the link form.
   */
  protected function getLinkForm() {
    $button = $this->getSession()->getPage()->find('css', '.cke_button__oslink');
    $button->click();
    $this->assertSession()->waitForElement('css', '#owl-field-text');
    return $this->getSession()->getPage()->find('css', '.wysiwyg-link-tool-wrapper');
  }

}
