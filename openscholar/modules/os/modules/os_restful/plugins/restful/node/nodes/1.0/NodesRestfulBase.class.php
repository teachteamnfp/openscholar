<?php

class NodesRestfulBase extends RestfulEntityBase {

  public static function controllersInfo() {
    return array(
      'bulk/terms' => array(
        RestfulInterface::POST => 'applyTerm',
        RestfulInterface::DELETE => 'removeTerm',
      ),
      'term/add' => array(
        RestfulInterface::POST => 'addTerm',
      )
    ) + parent::controllersInfo();
  }

  /**
  * Apply term tid's of selected nodes.
  */
  protected function applyTerm() {
    if (!empty($this->request['terms']) && !empty($this->request['nids'])) {
      $nodes = node_load_multiple($this->request['nids']);
      $new_terms = $this->request['terms'];
      $current_terms = array();
      foreach ($nodes as $key => $node) {
        $node_wrapper = entity_metadata_wrapper('node', $node);
        foreach ($node_wrapper->og_vocabulary->value() as $delta => $term_wrapper) {
          // $term_wrapper may now be accessed as a taxonomy term wrapper.
          $current_terms[] = $term_wrapper->tid;
        }
        $new_terms = array_unique(array_merge($current_terms, $new_terms));
        if (!empty($new_terms)) {
          $node_wrapper->og_vocabulary->set($new_terms);
          $node_wrapper->save();
          return array('saved' => true);
        }
      }
    }
    else {
      return array('saved' => false);
    }
  }

  /**
  * Remove term tid's of selected nodes.
  */
  protected function removeTerm() {
    // @TODO
    print_r($this->request);
   /* if (!empty($this->request['terms']) && !empty($this->request['nids'])) {
      $nodes = node_load_multiple($this->request['nids']);
      $terms = $this->request['terms'];
      foreach ($nodes as $key => $node) {
        $node_wrapper = entity_metadata_wrapper('node', $node);
        $node_wrapper->og_vocabulary->set($terms);
        $node_wrapper->save();
        return array('TermSaved' => true);
      }
    }
    else {
      return array('TermSaved' => false);
    }*/
  }

  /**
  * Create a taxonomy term and return the tid.
  */
  protected function addTerm() {
    if (!empty($this->request['vid']) && !empty($this->request['name'])) {
      $parent_id = 0;
      $term = new stdClass();
      $term->name = $this->request['name'];
      $term->vid = $this->request['vid'];
      $term->parent = array($parent_id);
      return array('saved' => taxonomy_term_save($term));
    }
    else {
      return array('saved' => false);
    }
  }

  /**
   * Define the bundles not to be exposed to the API.
   *
   * @var array
   *  Array keyed by bundle machine, and the RESTful resource as the value.
   */
  protected $bundles = array(
    'blog_import' => 'Blog entry import',
    'department' => 'Department Site',
    'feed_importer' => 'Feed importer',
    'personal' => 'Personal Site',
    'project' => 'Project Site',
    'slideshow_slide' => 'Slideshow Image'
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

  /**
   * Check if an operator is valid for filtering.
   *
   * @param array $operators
   *   The array of operators.
   *
   * @throws RestfulBadRequestException
   */
  protected static function isValidOperatorsForFilter(array $operators) {
    $allowed_operators = array(
      '=',
      '>',
      '<',
      '>=',
      '<=',
      '<>',
      '!=',
      'BETWEEN',
      'CONTAINS',
      'IN',
      'LIKE',
      'NOT IN',
      'STARTS_WITH',
    );
    foreach ($operators as $operator) {
      if (!in_array($operator, $allowed_operators)) {
        throw new \RestfulBadRequestException(format_string('Operator "@operator" is not allowed for filtering on this resource. Allowed operators are: !allowed', array(
          '@operator' => $operator,
          '!allowed' => implode(', ', $allowed_operators),
        )));
      }
    }
  }

   /**
   * Filter the query for list.
   *
   * @param \EntityFieldQuery $query
   *   The query object.
   *
   * @throws \RestfulBadRequestException
   *
   * @see \RestfulEntityBase::getQueryForList
   */
  protected function queryForListFilter(\EntityFieldQuery $query) {
    $public_fields = $this->getPublicFields();
    foreach ($this->parseRequestForListFilter() as $filter) {
      // Determine if filtering is by field or property.
      if (!$property_name = $public_fields[$filter['public_field']]['property']) {
        throw new \RestfulBadRequestException('The current filter selection does not map to any entity property or Field API field.');
      }
      if (field_info_field($property_name)) {
        if (in_array(strtoupper($filter['operator'][0]), array('IN', 'BETWEEN'))) {
          $query->fieldCondition($public_fields[$filter['public_field']]['property'], $public_fields[$filter['public_field']]['column'], $filter['value'], $filter['operator'][0]);
          continue;
        }
        for ($index = 0; $index < count($filter['value']); $index++) {
          $query->fieldCondition($public_fields[$filter['public_field']]['property'], $public_fields[$filter['public_field']]['column'], $filter['value'][$index], $filter['operator'][$index]);
        }
      }
      else {
        $column = $this->getColumnFromProperty($property_name);
        if (in_array(strtoupper($filter['operator'][0]), array('IN', 'BETWEEN'))) {
          $query->propertyCondition($column, $filter['value'], $filter['operator'][0]);
          continue;
        }
        for ($index = 0; $index < count($filter['value']); $index++) {
          $query->propertyCondition($column, $filter['value'][$index], $filter['operator'][$index]);
        }
      }
    }
  }

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

    $public_fields['og_vocabulary'] = array(
      'property' => og_vocabulary
    );

    $public_fields['vsite'] = array(
      'property' => OG_AUDIENCE_FIELD,
        'process_callbacks' => array(
          array($this, 'vsiteFieldDisplay'),
        ),
    );

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
   * Display the id and the title of the group.
   */
  public function vsiteFieldDisplay($value) {
    return array('title' => $value[0]->title, 'id' => $value[0]->nid);
  }

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

}
