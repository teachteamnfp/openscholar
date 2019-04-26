<?php

namespace Drupal\os_events\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\courier\CourierTokenElementTrait;
use Drupal\courier\Entity\TemplateCollection;
use Drupal\courier\Service\CourierManagerInterface;
use Drupal\rng\EventManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * CustomMessageForm class.
 */
class CustomMessageForm extends FormBase {
  use CourierTokenElementTrait;

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|EntityTypeManager
   */
  protected $entityManager;
  /**
   * The event manager service.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;
  /**
   * The courier manager service.
   *
   * @var \Drupal\courier\Service\CourierManagerInterface
   */
  protected $courierManager;

  /**
   * Constructs a MessageForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The routematch object.
   * @param \Drupal\rng\EventManagerInterface $eventManager
   *   The eventManager object.
   * @param \Drupal\courier\Service\CourierManagerInterface $courierManager
   *   The Courier manager object.
   */
  public function __construct(EntityTypeManager $entity_manager, MessengerInterface $messenger, RouteMatchInterface $route, EventManagerInterface $eventManager, CourierManagerInterface $courierManager) {
    $this->entityManager = $entity_manager;
    $this->messenger = $messenger;
    $this->routeMatch = $route;
    $this->eventManager = $eventManager;
    $this->courierManager = $courierManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('entity_type.manager'),
        $container->get('messenger'),
        $container->get('current_route_match'),
        $container->get('rng.event_manager'),
        $container->get('courier.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_message_to_registrations';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $courierChannel = $this->entityManager->getDefinition('courier_email');
    $form['#title'] = $courierChannel->getLabel();
    $t_args = [
      '@channel' => $courierChannel->getLabel(),
    ];

    /** @var \Drupal\courier\ChannelInterface $message */
    $message = $this->entityManager->getStorage($courierChannel->id())->create();
    $form_state->set('message_entity', $message);

    // Form display.
    $display = $this->entityManager->getStorage('entity_form_display')->load($courierChannel->id() . '.' . $courierChannel->id() . '.' . 'default');
    if (!$display) {
      $values = [
        'targetEntityType' => $courierChannel->id(),
        'bundle' => $courierChannel->id(),
        'mode' => 'default',
        'status' => TRUE,
      ];
      $display = $this->entityManager->getStorage('entity_form_display')
        ->create($values);
    }
    $form_state->set(['form_display'], $display);
    $form['message'] = [
      '#tree' => TRUE,
    ];
    $display->buildForm($message, $form['message'], $form_state);

    // Tokens.
    $form['tokens'] = [
      '#type' => 'container',
      '#title' => $this->t('Tokens'),
      '#weight' => 51,
    ];
    $form['tokens']['list'] = $this->courierTokenElement();

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send @channel', $t_args),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\courier\ChannelInterface $message */
    $message = $form_state->get('message_entity');
    $form_state->get(['form_display'])
      ->extractFormValues($message, $form, $form_state);
    $collection = TemplateCollection::create()
      ->setTemplate($message);
    $node = $this->routeMatch->getParameter('node');
    $eventMeta = $this->eventManager->getMeta($node);

    foreach ($eventMeta->getRegistrations() as $registration) {
      $collection->setTokenValue('registration', $registration);
      $collection->setTokenOption('clear', TRUE);
      $registrant = $registration->getRegistrants();
      $registrant = array_shift($registrant);
      $this->courierManager->sendMessage($collection, $registrant->getIdentity());
    }
    $this->messenger->addMessage($this->t('Messages queued for delivery.'));
  }

}
