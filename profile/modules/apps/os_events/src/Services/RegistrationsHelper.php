<?php

namespace Drupal\os_events\Services;

use DateInterval;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\os_events\RegistrationsHelperInterface;
use Drupal\rng\EventManagerInterface;
use Drupal\rng_date_scheduler\EventDateProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DefaultMailTemplate.
 *
 * @package Drupal\os_events\Services
 */
class RegistrationsHelper implements RegistrationsHelperInterface {

  use StringTranslationTrait;
  /**
   * Event Manager Service.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;
  /**
   * Event Date provider service.
   *
   * @var \Drupal\rng_date_scheduler\EventDateProvider
   */
  protected $dateProvider;
  /**
   * Request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Form Builder service.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * Constructs a new DefaultMailTemplate object.
   */
  public function __construct(EventManagerInterface $eventManager,
                              EventDateProviderInterface $dateProvider,
                              RequestStack $requestStack,
                              FormBuilder $formBuilder,
                              Connection $database,
                              Time $dateTime) {
    $this->eventManager = $eventManager;
    $this->dateProvider = $dateProvider;
    $this->requestStack = $requestStack;
    $this->formBuilder = $formBuilder;
    $this->database = $database;
    $this->dateTime = $dateTime;
  }

  /**
   * {@inheritdoc}
   */
  public function checkRegistrationStatus(array $build) : array {
    $data = [];
    $validDate = '';
    $full = $this->t("Sorry, the event is full");
    $closed = $this->t("Registration closed");
    $node = $build['#node'];
    $occurrences = 0;

    if (!$node->field_signup->value) {
      $data['message'] = $closed;
      return $data;
    }

    $dates = $this->dateProvider->getDates($node);
    $eventMeta = $this->eventManager->getMeta($node);

    $event_start_date = $node->field_recurring_date->value;
    $data['timestamp'] = strtotime($event_start_date);
    $event_start_date = DrupalDateTime::createFromTimestamp(strtotime($event_start_date));

    $occurrences = $this->getOccurrences($node->id());
    $now = DrupalDateTime::createFromTimestamp($this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME'));

    foreach ($occurrences as $occurrence) {
      $next = DrupalDateTime::createFromTimestamp(strtotime($occurrence->field_recurring_date_value));
      if ($next > $now && $event_start_date < $now) {
        $validDate = $next;
        $dateTimeObject = new DrupalDateTime($occurrence->field_recurring_date_value);
        $timestamp = $dateTimeObject->getTimestamp();
        $data['timestamp'] = $timestamp;
        break;
      }
    }

    // Check if it is a past event.
    if (!$validDate && $event_start_date < $now) {
      $data['message'] = $closed;
      return $data;
    }

    if (!$eventMeta->isAcceptingRegistrations()) {
      $data['message'] = $full;
    }
    $capacity = $eventMeta->remainingCapacity();
    $slot_available = FALSE;
    if ($capacity == -1 || $capacity > 0) {
      $slot_available = TRUE;
    }
    $data['message'] = (!$slot_available) ? $full : NULL;

    foreach ($dates as $date) {
      $open_date = ($date->getFieldName() == 'field_open_date') ? $date->getDate() : NULL;
      $close_date = ($date->getFieldName() == 'field_close_') ? $date->getDate() : NULL;

      if ($open_date && $now < $open_date) {
        $data['message'] = $closed;
      }
      if ($close_date && $now > $close_date) {
        $data['message'] = $closed;
      }
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function anotherDateLink(array $build) {
    $entity = $build['#node'];
    $id = $entity->id();
    $status = $this->checkRegistrationStatus($build);
    $occurrences = 0;

    // Generate select menu options for other dates available for registration.
    if ($entity->field_recurring_date->rrule &&
        !isset($status['message']) &&
        $entity->field_singup_multiple->value &&
        $this->requestStack->getCurrentRequest()->getPathInfo() != '/calendar') {
      $occurrences = $this->getOccurrences($id);

      foreach ($occurrences as $date) {
        $dateTimeObject = new DrupalDateTime($date->field_recurring_date_value);
        $timestamp = $dateTimeObject->getTimestamp();
        $offset = $dateTimeObject->getOffset();
        $interval = DateInterval::createFromDateString((string) $offset . 'seconds');
        $dateTimeObject->add($interval);
        if ($this->dateTime->getRequestTime() >= $timestamp) {
          continue;
        }
        if ($timestamp == $status['timestamp']) {
          continue;
        }
        $options[$timestamp] = $dateTimeObject->format('l, F j, Y');
      }
      $formData['nid'] = $id;
      $formData['options'] = $options;
      $data['date_switch'] = $this->formBuilder->getForm('Drupal\os_events\Form\MultipleDatesForm', $formData);

      // Another Date Dropdown Toggle Link.
      $url = Url::fromUserInput('#', [
        'attributes' => [
          'class' => ['toggle'],
        ],
      ]);
      $data['another_link'] = Link::fromTextAndUrl(' (another date?)', $url)->toString();
      return $data;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getOccurrences($id) {
    $query = $this->database->select('date_recur__node__field_recurring_date', 'dt')
      ->fields('dt', ['field_recurring_date_value'])
      ->condition('entity_id', $id);
    $occurrences = $query->execute()->fetchAll();
    return $occurrences;
  }

}
