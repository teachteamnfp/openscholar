<?php

namespace Drupal\os_publications;

/**
 * Defines possible modes for citation distribution.
 */
final class CitationDistributionModes {

  /**
   * Batch mode.
   *
   * In this mode, citations would be processed in background, or in a batch
   * process.
   */
  const BATCH = 'batch';

  /**
   * Per submission mode.
   *
   * In this mode, citations would be processed as soon as they are created,
   * updated, deleted.
   */
  const PER_SUBMISSION = 'per_submission';

}
