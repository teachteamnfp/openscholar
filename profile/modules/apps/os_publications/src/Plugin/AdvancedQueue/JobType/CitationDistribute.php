<?php

namespace Drupal\os_publications\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;

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

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    // TODO: Implement process() method.
  }

}
