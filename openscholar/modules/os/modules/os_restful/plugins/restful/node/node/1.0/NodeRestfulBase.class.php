<?php

class NodeRestfulBase extends OsbulkOperationEnitity {

  /**
   * Define the bundles not to be exposed to the API.
   *
   * @var array
   *  Array keyed by bundle machine, and the RESTful resource as the value.
   */
  protected $bundles = array(
    'bio' => 'Bio',
    'cv' => 'CV',
    'department' => 'Department Site',
    'personal' => 'Personal Site',
    'project' => 'Project Site',
    'slideshow_slide' => 'Slideshow Image',
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
  public function getQueryForList() {
    $query = parent::getQueryForList();
    $query->entityCondition('bundle', array_keys($this->getBundles()), 'NOT IN');
    $request = $this->getRequest();
    if ($request['vsite']) {
      $query->fieldCondition('og_group_ref', 'target_id', $request['vsite']);
    }
    return $query;
  }

  /**
   * Overrides RestfulEntityBase::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();
    $query->entityCondition('bundle', array_keys($this->getBundles()), 'NOT IN');
    $request = $this->getRequest();
    if ($request['vsite']) {
      $query->fieldCondition('og_group_ref', 'target_id', $request['vsite']);
    }
    return $query;
  }

  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    $public_fields['type'] = array(
      'property' => 'type',
    );

    $public_fields['publish_status'] = array(
      'property' => 'status',
      'process_callbacks' => array(
        array($this, 'processStatus'),
      ),
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

    $public_fields['og_vocabulary'] = array(
      'property' => og_vocabulary
    );

    $public_fields['vsite'] = array(
      'property' => OG_AUDIENCE_FIELD,
        'process_callbacks' => array(
          array($this, 'vsiteFieldDisplay'),
        ),
    );

    $public_fields['delete_access'] = array(
      'callback' => array($this, 'checkDeleteAccess'),
    );

    return $public_fields;
  }

  /**
   * Check entity access.
   *
   * @param \EntityDrupalWrapper $wrapper
   *   The wrapped entity.
   *
   * @return Boolean
   *   Delete status.
   */
  protected function checkDeleteAccess(\EntityDrupalWrapper $wrapper) {
    $entity = $wrapper->value();
    return $this->checkEntityAccess('delete', $this->entityType, $entity);
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
   * Display the id and the title of the group.
   */
  public function vsiteFieldDisplay($value) {
    return array('title' => $value[0]->title, 'id' => $value[0]->nid);
  }

  /**
   * Get gmdate.
   *
   * @param timestamp
   *   The enity's timestamp.
   *
   * @return string
   *   Formatted gmdate.
   */
  protected function dateFormat($timestamp) {
    return gmdate("Y-m-d\TH:i:s\Z", $timestamp);
  }

  /**
   * Get publish status as true or false.
   *
   * @param status
   *   The enity's status.
   *
   * @return bolean
   *   The formatted status.
   */
  protected function processStatus($status) {
    return $status = ($status == 1) ? true : false;
  }

}
