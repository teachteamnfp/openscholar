<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Test bibcite_reference post delete redirect behavior.
 *
 * @group functional
 * @group publications
 */
class BibciteReferenceDeleteRedirectTest extends TestBase {

  /**
   * @covers ::os_publications_form_bibcite_reference_confirm_form_alter
   * @covers ::os_publications_alter_post_bibcite_reference_delete_redirect
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $reference = $this->createReference();
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("bibcite/reference/{$reference->id()}/delete", $this->group);
    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertContains('/publications', $this->getSession()->getCurrentUrl());
  }

}
