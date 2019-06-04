<?php

namespace Drupal\cp_menu\Form\Multistep;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\cp_menu\Form\Multistep\Manager\StepManager;
use Drupal\cp_menu\Form\Multistep\Step\StepOne;
use Drupal\cp_menu\Form\Multistep\Step\StepTwo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuLinkAddForm.
 */
class MenuLinkAddForm extends FormBase {

  /**
   * Step Id.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepOne
   */
  protected $stepId;

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   */
  protected $step;

  /**
   * Step manager instance.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Manager\StepManager
   */
  protected $stepManager;

  /**
   * Constructs a \Drupal\cp_menu\Form\Multistep\MenuWizardBase instance.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store) {
    $this->stepId = StepOne::STEP_ONE;
    $this->privateTempStore = $private_temp_store;
    $this->store = $this->privateTempStore->get('link_data');
    $this->stepManager = new StepManager($this->privateTempStore);
  }

  /**
   * Inject all services we need.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_menu_link_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['wrapper-messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'messages-wrapper',
      ],
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'form-wrapper',
      ],
    ];

    // Get step from step manager.
    $this->step = $this->stepManager->getStep($this->stepId);

    // Attach step form elements.
    $form['wrapper'] += $this->step->buildStepFormElements();

    // Attach buttons.
    $form['wrapper']['actions']['#type'] = 'actions';

    // Buttons for Step one.
    if ($this->stepId === 1) {
      $form['wrapper']['actions']['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#goto_step' => StepTwo::STEP_TWO,
        '#ajax' => [
          'callback' => [$this, 'loadStep'],
          'event' => 'click',
          'wrapper' => 'form-wrapper',
        ],
      ];

      $form['wrapper']['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
      ];
    }

    // Buttons for Step Two.
    elseif ($this->stepId === 2) {

      $form['wrapper']['actions']['previous'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#goto_step' => StepOne::STEP_ONE,
        '#limit_validation_errors' => [],
        '#submit' => [],
        '#ajax' => [
          'callback' => [$this, 'loadStep'],
          'wrapper' => 'form-wrapper',
          'event' => 'click',
        ],
      ];

      $form['wrapper']['actions']['finish'] = [
        '#type' => 'submit',
        '#value' => $this->t('Finish'),
      ];

      $form['wrapper']['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
      ];
    }
    return $form;
  }

  /**
   * Ajax callback to load new step.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public static function loadStep(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Remove messages.
    $response->addCommand(new HtmlCommand('#messages-wrapper', ''));

    // Update Form.
    $response->addCommand(new HtmlCommand('#form-wrapper',
      $form['wrapper']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Save filled values to step. So we can use them as default_value later on.
    if ($this->stepId === 1) {
      $type = $form_state->getValue('link_type');
      $this->store->set('link_type', $type);
    }
    // Add step to manager.
    $this->stepManager->addStep($this->step);
    // Set step to navigate to.
    $triggering_element = $form_state->getTriggeringElement();
    $this->stepId = $triggering_element['#goto_step'];

    // If an extra submit handler is set, execute it.
    // We already tested if it is callable before.
    if (isset($triggering_element['#submit_handler'])) {
      $this->{$triggering_element['#submit_handler']}($form, $form_state);
    }
    $form_state->setRebuild(TRUE);
  }

}
