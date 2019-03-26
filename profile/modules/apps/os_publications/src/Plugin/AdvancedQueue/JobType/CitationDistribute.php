<?php

namespace Drupal\os_publications\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * The job type for distributing citations.
 *
 * @AdvancedQueueJobType(
 *   id = "os_publications_citation_distribute",
 *   label = @Translation("Citation Distribute"),
 *   max_retries = 10,
 *   retry_delay = 1,
 * )
 */
class CitationDistribute extends JobTypeBase {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $this->messenger()->addMessage('Citation distributed.');
    return JobResult::success();
  }

}
