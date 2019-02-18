<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Exception;
use PackagerMetsSwap;
use SWORDAPPClient;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Citation Distribute Sword base service.
 */
abstract class CitationDistributeSword implements CitationDistributionInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $serializer;

  /**
   * The citation styler service.
   *
   * @var \Drupal\bibcite\CitationStylerInterface
   */
  protected $styler;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $factory;

  /**
   * The packaging url.
   *
   * @var string
   */
  public $packaging = 'http://purl.org/net/sword-types/METSDSpaceSIP';

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->serializer = \Drupal::service('serializer');
    $this->styler = \Drupal::service('bibcite.citation_styler');
    $this->messenger = \Drupal::service('messenger');
    $this->database = \Drupal::service('database');
    $this->fileSystem = \Drupal::service('file_system');
    $this->loggerFactory = \Drupal::service('logger.factory');
  }

  /**
   * Get deposit url.
   *
   * @param array $workspaces
   *   Workspaces to deposit to.
   * @param int $id
   *   Entity id.
   *
   * @return mixed
   *   The url to deposit to.
   */
  abstract public function getDepositUrl(array $workspaces, $id);

  /**
   * Distributes a reference entity to chosen service.
   *
   * @param int $id
   *   Entity id to distribute.
   * @param array $plugin
   *   CD's definition of this plugin.
   *
   * @return bool
   *   Status of save/push.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function save($id, array $plugin) {
    // Doublecheck the validation for this nid.
    if (!$this->validate((int) $id)) {
      return FALSE;
    }

    $metadata = $this->mapMetadata($id);
    $out = $this->render($metadata, $id);
    $saved = $this->push($out, $id);

    return $saved;
  }

  /**
   * Copies data from bibcite entity data into array labeled for this service.
   *
   * @param int $id
   *   Entity id.
   *
   * @return array
   *   Mapping of metadata keys and values to distribute.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function mapMetadata($id) {
    $entity = $this->entityTypeManager->getStorage('bibcite_reference')->load($id);

    /* copy static options and direct mappings first */
    $metadata = [
      'Type' => 'http://purl.org/eprint/entityType/ScholarlyWork',
      'Title' => $entity->title->value,
      'Custodian' => $entity->bibcite_publisher->value,
      'Abstract' => $entity->bibcite_abst_e->value,
    ];

    /* Add each author to Creator metadata */
    $contributors = $entity->get('author') ?? [];
    foreach ($contributors as $reference) {
      $target_id = $reference->target_id;
      $contributor_obj = $this->entityTypeManager->getStorage('bibcite_contributor')->load($target_id);
      $metadata['Creator'][] = $contributor_obj->name->value;
    }

    /* Add each file as well */
    $files = $entity->get('field_files');
    if (empty($files)) {
      $citation_distribute_entity_object = &drupal_static('citation_distribute_entity_object');
      $files = $citation_distribute_entity_object->field_files;
    }
    foreach ($files as $file) {
      // $file = file_load($file->fid);
      // file load won't work yet, but data is already in db.
      $query = $this->database->select('file_managed', 'fm')
        ->condition('fid', $file->target_id)
        ->fields('fm', ['filemime', 'uri']);
      $f = $query->execute()->fetchAssoc();

      if ($f) {
        $metadata['File'][] = [
          'filepath' => $f['uri'],
          'filemime' => $f['filemime'],
        ];
      }
    }

    /* Create and add citation */
    $data = \Drupal::service('serializer')->normalize($entity, 'csl');
    $citation = \Drupal::service('bibcite.citation_styler')->render($data);
    $citation = strip_tags($citation);
    $citation = preg_replace('/&nbsp;/', ' ', $citation);
    $metadata['Citation'] = $citation;

    return $metadata;
  }

  /**
   * Themes data into format appropriate for this service.
   *
   * @param array $metadata
   *   Associative array of metadata keys and values to map.
   *
   * @return bool|string
   *   Returns false or path to file.
   *
   * @throws \Exception
   */
  public function render(array $metadata) {
    $id = func_get_arg(1);

    /* ensure we have a working directory or at least print some errors if we don't */
    $root_dir = $this->rootIn . '/' . $this->subdirIn . '/' . $id;
    if (!is_dir($root_dir)) {
      mkdir($root_dir, 0770, TRUE) || $this->messenger->addMessage($this->t('citation_distribute: could not create deposit directory @root_dir', [@root_dir => $root_dir], 'error'));
    }

    $zipfile = $id . '_' . $this->name . '-' . $this->fileOut;
    $packager = new PackagerMetsSwap($this->rootIn, $this->subdirIn, $this->rootOut, $zipfile);

    if (isset($metadata['File'])) {
      foreach ($metadata['File'] as $delta => $file) {
        $full = explode('/', $file['filepath']);
        $filename = end($full);
        copy($this->fileSystem->realpath($file['filepath']), $root_dir . '/' . $filename);
        $metadata['File'][$delta]['filepath'] = $id . '/' . $filename;
      }
    }
    else {
      // No file.
      return FALSE;
    }

    /* add metadata to mets xml */
    foreach ($metadata as $name => $data) {
      // Add to arrays.
      if (is_array($data)) {
        $func = 'add' . $name;
        foreach ($data as $delta => $d) {
          if ($name == 'File') {
            // addFile takes mime as well.
            $packager->$func($d['filepath'], $d['filemime']);
          }
          else {
            $packager->$func($d);
          }
        }
      }
      else {
        // Set scalars if they have content.
        if (strlen($data) > 0) {
          $func = 'set' . $name;
          $packager->$func(strip_tags($data));
        }
      }
    }

    /* attempt to write to xml file */
    try {
      $packager->create();
    }
    catch (Exception $e) {
      $this->loggerFactory->get('SWORD')->notice('Exception at: @file, @line: @message', [
        '@file' => $e->getFile(),
        '@line' => $e->getLine(),
        '@message' => $e->getMessage(),
      ]);
      $this->messenger->addMessage($this->t('service_sword: @error', [@error => $e->getMessage()]), 'error');
      return FALSE;
    }

    /* returns path to saved file */
    return $this->rootOut . '/' . $zipfile;
  }

  /**
   * Push to repository.
   *
   * @param string $file
   *   The file object.
   * @param int $id
   *   The entity id.
   *
   * @return bool
   *   True/False.
   *
   * @throws \Exception
   */
  private function push($file, $id) {
    $sac = new SWORDAPPClient();
    $sac_doc = $this->getServiceDoc($sac);
    $deposit_url = $this->getDepositUrl($sac_doc->sac_workspaces, $id);

    /* attempt a deposit */
    if ($deposit_url) {
      try {
        $deposit = $sac->deposit($deposit_url, $this->username, $this->password, $this->obo, $file, $this->packaging, 'application/zip');
      }
      catch (Exception $e) {
        $this->messenger->addMessage('DASH deposit error', 'error');
        $this->loggerFactory->get('SWORD')->notice('Exception at: @file, @line: @message', [
          '@file' => $e->getFile(),
          '@line' => $e->getLine(),
          '@message' => $e->getMessage(),
        ]);
        return FALSE;
      }
    }
    else {
      $this->messenger->addMessage('Unable to submit document to ' . $this->name . '.  No collection available for deposit.', 'error');
      return FALSE;
    }

    if ($deposit->sac_status < 200 || $deposit->sac_status >= 300) {
      debug($deposit, 'Push to Dash');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Gets the service document.
   *
   * @param \SWORDAPPClient $sac
   *   Sword app Object.
   *
   * @return bool|\SWORDAPPServiceDocument
   *   The service document.
   *
   * @throws \Exception
   */
  private function getServiceDoc(SWORDAPPClient $sac) {

    /* retrieve service document from sword server */
    $service_url = $this->location . '/sword/servicedocument';
    $sac_doc = $sac->servicedocument($service_url, $this->username, $this->password, $this->obo);
    /* check status of service doc.  fix errors if possible, bail if not */
    switch ($sac_doc->sac_status) {
      case 401:
        // Auth problems are usually a bad on-behalf-of request.  drop the obo.
        $sac_doc = $sac->servicedocument($service_url, $this->username, $this->password, '');
        // Only break if we're still unauthorized.  otherwise check other cases.
        if ($sac_doc->sac_status == 401) {
          $this->messenger->addMessage('Couldn\'t log in to SWORD server.', 'error');
          return FALSE;
        }
      case 200:
        // Logged in!
        break;

      default:
        // other, nonspecific error.
        $this->messenger->addMessage('Error ' . $sac_doc->sac_status . ' connecting to sword server', 'error');
        $this->loggerFactory->get('SWORD')->notice('HTTP @code Error connecting to SWORD server', [
          '@code' => $sac_doc->sac_status,
        ]);
        return FALSE;
    }

    return $sac_doc;
  }

}
