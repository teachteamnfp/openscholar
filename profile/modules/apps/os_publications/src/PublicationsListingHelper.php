<?php

namespace Drupal\os_publications;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\redirect\Entity\Redirect;
use Drupal\redirect\RedirectRepository;

/**
 * PublicationsListingHelper.
 */
final class PublicationsListingHelper implements PublicationsListingHelperInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * PublicationsListingHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\redirect\RedirectRepository $redirect_repository
   *   Redirect repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RedirectRepository $redirect_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->redirectRepository = $redirect_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function convertLabel(string $label) : string {
    $words_to_trim = [
      'the',
      'a',
      'an',
      'about',
      'beside',
      'near',
      'to',
      'above',
      'between',
      'of',
      'towards',
      'across',
      'beyond',
      'off',
      'under',
      'after',
      'by',
      'on',
      'underneath',
      'against',
      'despite',
      'onto',
      'unlike',
      'along',
      'down',
      'opposite',
      'until',
      'among',
      'during',
      'out',
      'up',
      'around',
      'except',
      'outside',
      'upon',
      'as',
      'for',
      'over',
      'via',
      'at',
      'from',
      'past',
      'with',
      'before',
      'in',
      'round',
      'within',
      'behind',
      'inside',
      'since',
      'without',
      'below',
      'into',
      'than',
      'beneath',
      'like',
      'through',
    ];

    $pattern = '/\b^(?:' . implode('|', $words_to_trim) . ')\b/i';

    return mb_strtoupper(substr(trim(preg_replace($pattern, '', mb_strtolower($label))), 0, 1));
  }

  /**
   * {@inheritdoc}
   */
  public function convertAuthorName(string $name): string {
    return mb_strtoupper(substr($name, 0, 1));
  }

  /**
   * {@inheritdoc}
   */
  public function setRedirect(string $source, string $redirect): Redirect {
    /** @var \Drupal\redirect\Entity\Redirect[] $redirects */
    $redirects = $this->redirectRepository->findBySourcePath($source);
    /** @var \Drupal\Core\Entity\EntityStorageInterface $entity_storage */
    $entity_storage = $this->entityTypeManager->getStorage('redirect');

    $entity_storage->delete($redirects);

    if ($redirect === 'title') {
      return NULL;
    }

    $redirect = Redirect::create([
      'redirect_source' => $source,
      'redirect_redirect' => "internal:/$source/$redirect",
      'status_code' => 301,
    ]);
    $redirect->save();

    return $redirect;
  }

}
