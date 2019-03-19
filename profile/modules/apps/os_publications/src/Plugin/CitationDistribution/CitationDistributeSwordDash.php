<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Citation Distribute GoogleScholar service.
 *
 * //@CitationDistribute(
 *   id = "citation_distribute_sword_dash",
 *   title = @Translation("Sword based Dash citation distribute service."),
 *   href = "http://dash.harvard.edu",
 *   name = "Dash",
 *   description = "Harvard's open access repository",
 *   href = "https://dash.harvard.edu",
 *   formclass = "CitationDashConfigForm"
 * )
 */
class CitationDistributeSwordDash extends CitationDistributeSword {

  /**
   * Repository name.
   *
   * @var string
   */
  public $name = 'dash';

  /**
   * Repository title.
   *
   * @var string
   */
  public $title = 'DASH';

  /**
   * Repository location url.
   *
   * @var string
   */
  public $location = 'https://dash.harvard.edu';

  /**
   * The username.
   *
   * @var array|mixed|null
   */
  public $username;

  /**
   * The password.
   *
   * @var array|mixed|null
   */
  public $password;

  /**
   * The root in directory.
   *
   * @var string
   */
  public $rootIn = '/tmp/sword';

  /**
   * The Subdirectory.
   *
   * @var string
   */
  public $subdirIn = 'dash';

  /**
   * The root out directory.
   *
   * @var string
   */
  public $rootOut = '/tmp/sword';

  /**
   * The file output name.
   *
   * @var string
   */
  public $fileOut = 'dash_files.zip';

  /**
   * On behalf of.
   *
   * @var string
   */
  public $obo = '';

  /**
   * CitationDistributeSwordDash constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configfactory service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteManager
   *   The vsitemanager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
  ConfigFactoryInterface $configFactory,
                              VsiteContextManagerInterface $vsiteManager) {
    parent::__construct();
    $this->config = $configFactory->get('dash.settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->vsiteManager = $vsiteManager;
    $this->username = $this->config->get('dash.username');
    $this->password = $this->config->get('dash.password');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $container->get('entity_type.manager'),
          $container->get('config.factory'),
          $container->get('vsite.context_manager')
        );
  }

  /**
   * OR it can take a nid, depending on when the validation takes place.
   */
  public function validate($id) {

    if (is_array($id)) {
      // $files = $id['values']['field_upload'];.
    }
    elseif (is_int($id) && $entity = $this->entityTypeManager->getStorage('bibcite_reference')->load($id)) {
      $files = $entity->field_files;
      $citation_distribute_entity_object = &drupal_static('citation_distribute_entity_object');
      if ($entity->get('field_files')->isEmpty() && isset($citation_distribute_entity_object)) {
        // During entity insert, files may not be availabe.
        // if so, use staticly cached entity object.
        $files = $citation_distribute_entity_object->get('field_files');
      }
    }
    else {
      // Not sure what id is anymore, so bail.
      return FALSE;
    }

    // If (count($files) > 0) { // or body-> full text?
    // has a file been added?
    if ($files) {
      return TRUE;
    }
    else {
      // TODO form validation.
      // $form_state->setErrorByName('files', $this->title . '
      // requires the full text document be uploaded as an attachment.
      // Please upload your document in the
      // Attach Files section of this form.').;.
      return FALSE;
    }
  }

  /**
   * GetDepositUrl($workspace) returns deposit url given workspaces/collections.
   *
   * Try to figure out the correct collection.
   */
  public function getDepositUrl(array $workspaces, $id) {
    // Get email address.
    $group = $this->vsiteManager->getActiveVsite();
    $email = $group->getOwner()->getEmail();
    // Get department of email domain.
    $domain = end(explode('@', $email));
    $department = (isset($this->departmentDomains[$domain])) ? self::DEPARTMENTDOMAINS[$domain] : self::DEPARTMENTDOMAINS['harvard.edu'];

    foreach ($workspaces as $workspace) {
      foreach ($workspace->sac_collections as $collection) {
        if ($collection->sac_colltitle == $department) {
          // Yes this is a simplexmlelement.  yes curl and/or sword can read it.
          $deposit_url = $collection->sac_href;
        }
      }
    }

    if (isset($deposit_url)) {
      return $deposit_url;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Department domains.
   */
  const DEPARTMENTDOMAINS = [
    'fas.harvard.edu' => 'FAS Scholarly Articles',
    'harvard.edu' => 'FAS Scholarly Articles',
    'iq.harvard.edu' => 'FAS Scholarly Articles',
    'lists.iq.harvard.edu' => 'FAS Scholarly Articles',
    'hmdc.harvard.edu' => 'FAS Scholarly Articles',

    'hsps.harvard.edu' => 'HSPH Scholarly Articles',

    'hms.harvard.edu' => 'HMS Scholarly Articles',

    'law.harvard.edu' => 'HLS Scholarly Articles',
    'mail.law.harvard.edu' => 'HLS Scholarly Articles',
    'llm11.law.harvard.edu' => 'HLS Scholarly Articles',
    'jd13.law.harvard.edu' => 'HLS Scholarly Articles',

    'hds.harvard.edu' => 'HDS Scholarly Articles',

    'gse.harvard.edu' => 'GSE Scholarly Articles',

    'gsd.harvard.edu' => 'GSD Scholarly Articles',
  ];

  /**
   * {@inheritdoc}
   */
  public function delete(EntityInterface $entity) {
    // TODO: Implement delete() method.
  }

}
