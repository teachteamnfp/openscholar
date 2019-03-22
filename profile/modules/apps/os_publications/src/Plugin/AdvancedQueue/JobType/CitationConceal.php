<?php

namespace Drupal\os_publications\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * The job type for concealing citations.
 *
 * @AdvancedQueueJobType(
 *   id = "os_publications_citation_conceal",
 *   label = @Translation("Citation Conceal"),
 *   max_retries = 10,
 *   retry_delay = 1,
 * )
 */
class CitationConceal extends JobTypeBase {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $this->messenger()->addMessage('Citation concealed.');
    return JobResult::success();
  }

}
