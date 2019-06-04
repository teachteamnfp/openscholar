<?php

namespace Drupal\cp_menu\Form\Multistep;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\cp_menu\Form\Multistep\Manager\StepManager;
use Drupal\cp_menu\Form\Multistep\Step\StepOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuLinkAddForm.
 */
class MenuLinkAddForm extends FormBase {

  /**
   * Temporary storage.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;
  /**
   * Private temp store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

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
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('multistep_data');
    $this->stepId = StepOne::STEP_ONE;
    $this->stepManager = new StepManager();
  }

  /**
   * {@inheritdoc}
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function loadStep(array &$form, FormStateInterface $form_state) {
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
    $form_values = $form_state->getValues();
    foreach ($form_values as $name => $value) {
      $values[$name] = $value;
    }
    $this->step->setValues($values);
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
