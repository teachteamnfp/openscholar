<?php

class RestfulAppform extends RestfulBase implements \RestfulDataProviderInterface {

  /**
   * Overrides \RestfulBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::GET => 'getAppStates',
        \RestfulInterface::PATCH => 'setAppStates'
      ),
    );
  }

  /**
   * Overrides \RestfulInterface::publicFieldsInfo().
   *
   * Empty function to fulfill interface requirements.
   */
  public function publicFieldsInfo() {
    return array();
  }

  /**
   * Handle activating the space for access and variable override purposes
   * @return bool - TRUE if the space activated
   */
  protected function activateSpace() {
    if ($_GET['vsite'] && $vsite = vsite_get_vsite($_GET['vsite'])) {
      // Make sure the Drupal $user account is the account Restful authenticated
      $account = $this->getAccount();
      spaces_set_space($vsite);
      $vsite->activate_user_roles();
      $vsite->init_overrides();
      return true;
    }
    return false;
  }

  public function getAppStates() {
    $this->activateSpace();
    $features = variable_get('spaces_features', array());
    $info = spaces_features('og');

    $output = array();
    foreach ($info as $name => $i) {
      $output[] = array(
        'title' => $i->info['name'],
        'machine_name' => $name,
        'description' => $i->info['description'],
        'enabled' => $features[$name] ? 1 : 0,
        'private' => $features[$name] ? $features[$name] - 1 : 0
      );
    }

    return $output;
  }

  public function setAppStates() {
    $this->activateSpace();

    $original = variable_get('spaces_features', array());
    foreach ($this->request as $key => $val) {
      if (is_numeric($key)) {
        $name = $val['machine_name'];
        if ($val['enabled']) {
          if ($val['private']) {
            $original[$name] = 2;
          }
          else {
            $original[$name] = 1;
          }
        }
        else {
          $original[$name] = 0;
        }
      }
    }

    if (module_exists('vsite') && $vsite = vsite_get_vsite()) {
      $vsite->controllers->variable->set('spaces_features', $original);
    }
    else {
      variable_set('spaces_features', $original);
    }
  }
}
