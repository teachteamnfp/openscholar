<?php

namespace Drupal\os_events\Services;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormState;
use Drupal\rng\Entity\Rule;
use Drupal\rng\Entity\RuleComponent;
use Exception;

/**
 * Class DefaultMailTemplate.
 *
 * @package Drupal\os_events\Services
 */
class DefaultMailTemplate {


  /**
   * Action Manager service.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * Entity Manager type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Constructs a new DefaultMailTemplate object.
   *
   * @param \Drupal\Core\Action\ActionManager $actionManager
   *   Action manager service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityManager
   *   Entity Manager service.
   */
  public function __construct(ActionManager $actionManager, EntityTypeManager $entityManager) {
    $this->actionManager = $actionManager;
    $this->entityManager = $entityManager;
  }

  /**
   * Creates and enables default mail template when Event node is created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Then Node object.
   *
   * @throws PluginException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws Exception
   */
  public function createDefaultTemplate(EntityInterface $node) {
    /** @var \Drupal\rng\Plugin\Action\CourierTemplateCollection $actionPlugin */
    $actionPlugin = $this->actionManager->createInstance('rng_courier_message');

    // Create a new template collection by faking a form submission.
    $dummy = [];
    $actionPlugin->submitConfigurationForm($dummy, new FormState());
    $template_collection = $actionPlugin->getTemplateCollection();

    // Get the email templates so we can modify them.
    $templates = $template_collection->getTemplates();

    /** @var \Drupal\courier\Entity\Email $mail_template */
    $mail_template = $templates[0];
    // Generating mail body.
    $message = 'Hello [identity:label] </br></br>';
    $message .= 'You have been registered for [node:title]';
    if ($node->field_recurring_date->value) {
      $message .= ' on [node:field_recurring_date:start_date:long] </br>';
    }
    if ($node->field_location->value) {
      $message .= ' at [node:field_location]';
    }
    $message .= ".</br></br>";
    $message .= 'View the event listing here: </br> [node:url] </br></br>';
    $message .= 'Questions? Email the event organizer [current-user:mail] </br></br>';
    $message .= 'See you there! </br></br></br>';
    $message .= 'Harvard University, Cambridge, MA 02138';

    $mail_template->setSubject('[node:title] event registration confirmation');
    $mail_template->setBody($message);
    $mail_template->save();

    // Save the mail template collection for this event.
    $context = $this->entityManager->getStorage('courier_context')
      ->load('rng_registration_' . $node->getEntityTypeId());
    if (!$context) {
      throw new Exception(sprintf('No context available for %s', $node->getEntityTypeId()));
    }
    /** @var \Drupal\courier\CourierContextInterface $context */
    $template_collection->setContext($context);
    $template_collection->setOwner($node);
    $template_collection->save();

    // Set the action to send mail on new registrations.
    $action = RuleComponent::create([])
      ->setPluginId($actionPlugin->getPluginId())
      ->setConfiguration($actionPlugin->getConfiguration())
      ->setType('action');
    $rule = Rule::create([
      'event' => ['entity' => $node],
      'trigger_id' => 'entity:registration:new',
    ]);
    // Make rule active.
    $rule->setIsActive(TRUE)
      ->save();
    // Save the action.
    $action->setRule($rule)->save();
  }

}
