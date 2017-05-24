<?php

class NodesRestfulBase extends RestfulEntityBase {


  /**
   * Define the bundles to exposed to the API.
   *
   * @var array
   *  Array keyed by bundle machine, and the RESTful resource as the value.
   */
  protected $bundles = array(
    'personal' => 'personal',
    );

  /**
   * Return the bundles.
   *
   * @return array
   *  An array of the exposed bundles as key and resource as value.
   */
  protected function getBundles() {
    return $this->bundles;
  }

  /**
   * Overrides RestfulEntityBase::getQueryForList().
   */
  /*public function getQueryForList() {
    $query = parent::getQueryForList();
    $query->entityCondition('bundle', array_keys($this->getBundles()), 'NOT IN');
    return $query;
  }*/



  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    $public_fields['type'] = array(
      'property' => 'type',
    );

    $public_fields['publish_status'] = array(
      'property' => 'status',
    );

    $public_fields['author'] = array(
      'property' => 'author',
      'sub_property' => 'name',
    );

    $public_fields['changed'] = array(
      'property' => 'changed',
      'process_callbacks' => array(
        array($this, 'dateFormat'),
      ),
    );

    $public_fields['link'] = array(
      'callback' => array($this, 'getEntityLink'),
    );

    /*$public_fields['vsite'] = array(
      'callback' => array($this, 'getEntityVsiteId'),
    );*/

    return $public_fields;
  }

  /**
   * Get entity's link.
   *
   * @param \EntityDrupalWrapper $wrapper
   *   The wrapped entity.
   *
   * @return string
   *   The link URL.
   */
  protected function getEntityLink(\EntityDrupalWrapper $wrapper) {
    $values = $wrapper->value();
    return l(t($values->title), "node/$values->nid");
  }

  /**
   * Get entity's vsite id.
   *
   * @param \EntityDrupalWrapper $wrapper
   *   The wrapped entity.
   *
   * @return interger
   *   vsite id.
   */
  /*protected function getEntityVsiteId(\EntityDrupalWrapper $wrapper) {
    $values = $wrapper->value();
    return $values->og_group_ref[LANGUAGE_NONE][0]['target_id'];
  }*/

  /**
   * Get formatted date and time.
   *
   * @param timestamp
   *   The enity's timestamp.
   *
   * @return string
   *   The formatted date.
   */
  protected function dateFormat($timestamp) {
    return format_date($timestamp, $type = 'long');
  }

  /*protected function checkEntityAccess($op, $entity_type, $entity) {
    $request = $this->getRequest();

    if ($request['vsite']) {
      spaces_set_space(spaces_load('og', $request['vsite']));
    }

    if (empty($entity->nid)) {
      // This is still a new node. Skip.
      return;
    }

    if ($is_group = og_is_group($entity_type, $entity)) {
      $group = $entity;
    }
    else {
      $wrapper = entity_metadata_wrapper('node', $entity);
      $group = $wrapper->{OG_AUDIENCE_FIELD}->get(0)->value();
    }

    if (empty($request['vsite'])) {
      spaces_set_space(spaces_load('og', $group->nid));
    }

    $manager = og_user_access('node', $group->nid, 'administer users', $this->getAccount());

    if ($is_group) {
      // In addition to the node access check, we need to see if the user can
      // manage groups.
      return $manager && !vsite_access_node_access($group, 'view', $this->getAccount()) == NODE_ACCESS_DENY;
    }
    else {
      $app = os_get_app_by_bundle($entity->type);
      $space = spaces_get_space();
      $application_settings = $space->controllers->variable->get('spaces_features');

      switch ($application_settings[$app]) {
        case OS_DISABLED_APP:
          return FALSE;

        case OS_PRIVATE_APP:
          return og_is_member('node', $group->nid, 'user', $this->getAccount()) && parent::checkEntityAccess($op, $entity_type, $entity);

        default:
        case OS_PUBLIC_APP:
          return parent::checkEntityAccess($op, $entity_type, $entity);
      }
    }
  }*/

}
