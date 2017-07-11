<?php

class OsTaxonomyTerm extends OsRestfulEntityCacheableBase {

  public static function controllersInfo() {
    return array(
      'term/add' => array(
        RestfulInterface::POST => 'addTerm'
      )
    ) + parent::controllersInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function publicFieldsInfo() {
    $fields = parent::publicFieldsInfo();


    $fields['vocab'] = array(
      'property' => 'vocabulary',
      'process_callbacks' => array(
        function($vocabulary) {
          return $vocabulary->machine_name;
        }
      ),
    );

    $fields['vocabName'] = array(
      'property' => 'vocabulary',
      'process_callbacks' => array(
        function($vocabulary) {
          return $vocabulary->name;
        }
      ),
    );

    $fields['vid'] = array(
      'property' => 'vocabulary',
      'process_callbacks' => array(
        function($vocabulary) {
          return $vocabulary->vid;
        }
      ),
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle() {
    if ($this->path && !$this->fetchingUpdates()) {
      $wrapper = entity_metadata_wrapper('taxonomy_term', $this->path);
      return $wrapper->vocabulary->machine_name->value();
    }

    return $this->bundle;
  }

  /**
   * Display the name of the vocab from the vocabulary object.
   *
   * @param $value
   *   The vocabulary object.
   *
   * @return mixed
   *   The machine name of the vocabulary.
   */
  protected function processVocab($value) {
    return $value->machine_name;
  }

  /**
   * {@inheritdoc}
   *
   * Display taxonomy terms from the current vsite.
   */
  protected function queryForListFilter(\EntityFieldQuery $query) {

    if (empty($_GET['vsite'])) {
      throw new \RestfulBadRequestException(t('You need to provide a vsite.'));
    }

    if (!$vsite = vsite_get_vsite($this->request['vsite'])) {
      return;
    }

    module_load_include('inc', 'vsite_vocab', 'includes/taxonomy');
    $vocabData = vsite_vocab_get_vocabularies($vsite);
    $requested = array();
    $badVocabs = array();

    if (!empty($this->request['vocab'])) {
      $condition = is_array($this->request['vocab']) ? $this->request['vocab'] : array($this->request['vocab']);
      foreach ($vocabData as $v) {
        if (in_array($v->machine_name, $condition)) {
          $requested[] = $v->vid;
          $condition = array_diff($condition, array($v->machine_name));
        }
      }
      $badVocabs = $condition;
    }
    elseif (!empty($this->request['vid'])) {
      $condition = is_array($this->request['vid']) ? $this->request['vid'] : array($this->request['vid']);
      foreach ($condition as $vid) {
        if (isset($vocabData[$vid])) {
          $requested[] = $vid;
        }
        else {
          $badVocabs[] = $vid;
        }
      }
    }
    elseif (!empty($this->request['entity_id']) && !empty($this->request['entity_type'])) {
      // Load only enabled vocabularies of seclected content type.
      //$nodes = node_load_multiple($this->request['nids']);
      $entity_type = $this->request['entity_type'];
      $entity_id = $this->request['entity_id'];
      $entities = entity_load($entity_type, $entity_id);
      $request_bundle = array();
      $enabled_bundles = array();
      foreach ($entities as $key => $entity) {
        $request_bundle[] = $entity->type;
      }
      $request_bundle = array_unique($request_bundle);
      // Transform content type name from machine name to human readable
      // format.
      $content_types = array_map('ucfirst', $request_bundle);
      $content_types = implode(', ', $content_types);
      $content_types = str_replace('_', ' ', $content_types);

      foreach ($request_bundle as $key => $bundle) {
        $og_vocab = new EntityFieldQuery();
        $og_vocab = $og_vocab
          ->entityCondition('entity_type', 'og_vocab')
          ->propertyCondition('bundle', $bundle)
          ->execute();
        $og_vocab = array_keys($og_vocab['og_vocab']);
        $entities = entity_load('og_vocab', $og_vocab);
        foreach ($entities as $key => $entity) {
          if ($entity->vid) {
            $requested[] = $entity->vid;
            $enabled_bundles[] = $entity->bundle;
          }
        }
      }
      if (count($request_bundle) > 1) {
        $requested = array_unique(array_diff_assoc($requested, array_unique($requested)));
        if (empty($requested)) {
          throw new \RestfulBadRequestException(format_string('@bundles do not share the same vocabularies.', array('@bundles' => $content_types)));
        }
        foreach ($request_bundle as $key => $bundle) {
          if (!in_array($bundle, $enabled_bundles)) {
            throw new \RestfulBadRequestException(format_string('@bundles do not share the same vocabularies.', array('@bundles' => $content_types)));
          }
        }
      }
      if (empty($requested)) {
        throw new \RestfulBadRequestException(format_string('No vocabularies enabled for @bundles content type.', array('@bundles' => $content_types)));
      }
    }
    else {
      // no filtered vocabs requested, so return everything based on the vsite.
      $requested = array_keys($vocabData);
    }

    if (empty($requested)) {
      throw new \RestfulBadRequestException(format_string('The vocab(s) @vocab you asked for is not part of the vsite.', array('@vocab' => explode(', ', $badVocabs))));
    }
    $query->propertyCondition('vid', $requested, 'IN');

  }

  /**
   * {@inheritdoc}
   */
  public function entityValidate(\EntityMetadataWrapper $wrapper) {
    if (!$this->getRelation($wrapper->value())) {
      // The vocabulary is not relate to any group.
      throw new \RestfulBadRequestException("The vocabulary isn't relate to any group.");
    }

    parent::entityvalidate($wrapper);
  }

  /**
   * {@inheritdoc}
   */
  protected function isValidEntity($op, $entity_id) {
    // The entity is valid since it's already been filtered in
    // self::queryForListFilter() and the access is checked in
    // self::checkEntityAccess().
    return true;
  }

  /**
   * Overrides RestfulEntityBaseTaxonomyTerm::checkEntityAccess().
   */
  protected function checkEntityAccess($op, $entity_type, $entity) {

    if (!$relation = $this->getRelation($entity)) {
      return FALSE;
    }

    if ($op == 'view') {
      return TRUE;
    }

    // We need this in order to alter the global user object.
    $this->getAccount();

    spaces_set_space(vsite_get_vsite($relation->gid));

    if (!vsite_og_user_access('administer taxonomy')) {
      throw new \RestfulBadRequestException("You are not allowed to create terms.");
    }
  }

  /**
   * Get the vocabulary relation from request.
   *
   * @return mixed
   *   OG vocab relation.
   */
  private function getRelation($entity) {
    $vocab = empty($entity->vocabulary_machine_name) ? $this->request['vocab'] : $entity->vocabulary_machine_name;
    $this->bundle = $vocab;
    return og_vocab_relation_get(taxonomy_vocabulary_machine_name_load($vocab)->vid);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkPropertyAccess($op, $public_field_name, EntityMetadataWrapper $property_wrapper, EntityMetadataWrapper $wrapper) {
    // We need this in order to alter the global user object.
    $this->getAccount();

    if ($op != 'view') {
      if (module_exists('spaces')) {
        $relation = $this->getRelation(taxonomy_term_load($this->path));
        spaces_set_space(vsite_get_vsite($relation->vid));
        return vsite_og_user_access('administer taxonomy');
      }
      return parent::checkPropertyAccess($op, $public_field_name, $property_wrapper, $wrapper);
    }
    else {
      // By default, Drupal restricts access to even viewing vocabulary properties.
      // There's really no case where viewing a vocabular property is a problem though
      return true;
    }

  }

  protected function getLastModified($id) {
    // Vocabularies cannot really be editted. When they were first created isn't stored either.
    // This function is only concerned with modifications, so as long as we assume it's really old, we're fine for now
    return strotime('-31 days', REQUEST_TIME);
  }

  /**
   * Create a taxonomy term and return tid.
   */
  protected function addTerm() {
    if (!empty($this->request['vid']) && !empty($this->request['name'])) {
      $parent_id = 0;
      $term = new stdClass();
      $term->name = $this->request['name'];
      $term->vid = $this->request['vid'];
      $term->parent = array($parent_id);
      taxonomy_term_save($term);
      return array('term_id' => $term->tid);
    }
    else {
      return array('term_id' => false);
    }
  }

}
