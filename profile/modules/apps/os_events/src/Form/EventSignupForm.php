<?php

namespace Drupal\os_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\rng\Entity\Registrant;
use Drupal\rng\Entity\Registration;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\RegistrantFactoryInterface;
use Drupal\rng_contact\Entity\RngContact;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the ModalForm for Signup.
 */
class EventSignupForm extends FormBase {

  /**
   * Constructs EventSignup object.
   *
   * @param \Drupal\rng\Entity\RegistrantFactoryInterface $registrantFactory
   *   The registrant factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The EntityManager service.
   * @param \Drupal\rng\EventManagerInterface $eventManager
   *   The Event Manager service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger service.
   */
  public function __construct(RegistrantFactoryInterface $registrantFactory,
                              EntityTypeManagerInterface $entityManager,
                              EventManagerInterface $eventManager,
                              Messenger $messenger) {
    $this->registrantFactory = $registrantFactory;
    $this->entityManager = $entityManager;
    $this->eventManager = $eventManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('rng.registrant.factory'),
      $container->get('entity_type.manager'),
      $container->get('rng.event_manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'events_signup_modal_form';
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '50%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL, $timestamp = NULL) {

    // Add the core AJAX library.
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#prefix'] = '<div id = "signup-modal-form">';
    $form['#suffix'] = '</div>';

    if ($timestamp) {
      $dateTimeObject = DrupalDateTime::createFromTimestamp($timestamp);
      $offset = $dateTimeObject->getOffset();
      $interval = \DateInterval::createFromDateString((string) $offset . 'seconds');
      $dateTimeObject->add($interval);
      $form['field_repeating_event_date_text'] = [
        '#markup' => '<span class="date-display-single">' . $this->t('On @date', ['@date' => $dateTimeObject->format('l, F j, Y H:i:s')]) . "</span>",
        '#weight' => -10,
      ];

      $form['registering_for_date'] = [
        '#type' => 'hidden',
        '#value' => $timestamp,
      ];
    }

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#size' => 40,
    ];

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#size' => 40,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#size' => 40,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Signup'),
      '#ajax' => [
        'callback' => '::ajaxSubmitForm',
        'event' => 'click',
      ],
    ];
    $form['#cache'] = ['max-age' => 0];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $node = $this->entityManager->getStorage('node')->load($form_state->getValue('nid'));
    $eventMeta = $this->eventManager->getMeta($node);
    $registrations = $eventMeta->getRegistrations();
    $registrants = $eventMeta->getRegistrants('rng_contact');
    $emailEntered = $form_state->getValue('email');
    $for_date = $form_state->getValue('registering_for_date');

    foreach ($registrations as $registration) {
      $date = $registration->field_for_date->value;
      foreach ($registrants as $registrant) {
        $id = $registrant->identity->getValue()[0]['target_id'];
        $identity = $this->entityManager->getStorage('rng_contact')->load($id);
        $email = $identity->field_email->value;
        if ($email === $emailEntered && $date === $for_date) {
          $form_state->setErrorByName('email', $this->t('User is already registered for this date.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * Implements the submit handler for the modal dialog AJAX call.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array of AJAX commands to execute on submit of the modal form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\rng\Exception\InvalidEventException
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getErrors()) {
      $response->addCommand(new ReplaceCommand('#signup-modal-form', $form));
      $this->messenger()->deleteAll();
    }

    else {
      $values = $form_state->getValues();
      $node = $this->entityManager->getStorage('node')->load($form_state->getValue('nid'));

      // Create registration and registrant.
      $this->createRegistration($values, $node);

      $eventMeta = $this->eventManager->getMeta($node);

      // Check if capacity is full,replace Signup with relevant message.
      $capacity = $eventMeta->remainingCapacity();
      ($capacity == -1) ? $slot_available = TRUE : ($capacity > 0) ? $slot_available = TRUE : $slot_available = FALSE;
      if (!$slot_available) {
        $id = 'registration-link-' . $node->id();
        $message = '<div id="' . $id . '">' . $this->t("Sorry, the event is full") . '</div>';
        $response->addCommand(new ReplaceCommand('#' . $id, $message));
      }
      $response->addCommand(new CloseModalDialogCommand());
    }
    // Finally return our response.
    return $response;
  }

  /**
   * Creates registrations, registrants and identities.
   *
   * @param array $values
   *   Form values entered vy the user.
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRegistration(array $values, EntityInterface $node) {
    $date = $values['registering_for_date'];

    $registration = Registration::create([
      'type' => 'signup',
      'event' => $node,
      'field_for_date' => $date,
    ]);
    $registration->save();

    $identity = RngContact::create([
      'type' => 'anonymous_',
      'label' => $values['full_name'],
      'field_email' => $values['email'],
      'field_department' => $values['department'],
    ]);
    $identity->save();

    $registrant = Registrant::create([
      'type' => 'registrant',
      'registration' => $registration,
      'identity' => $identity,
    ]);
    $registrant->save();
  }

}
