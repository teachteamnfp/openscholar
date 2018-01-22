<?php

class OsSearchApiSolrService extends SearchApiSolrService
{
    /**
     * {@inheritdoc}
     */
    public function search(SearchApiQueryInterface $query)
    {
        if (module_exists('vsite') && ($vsite = spaces_get_space())) {
            $vsite_filter = $query->createFilter('OR');

            // This site.
            $vsite_filter->condition('og_group_ref', $vsite->group_type . ":" . $vsite->id);

            if (variable_get('os_search_solr_include_subsites') || variable_get('os_search_solr_search_sites')) {
                ctools_include('subsite', 'vsite');

                if (variable_get('os_search_solr_include_subsites')) {
                    // Get Subsites.
                    $subsites = vsite_get_subsites($vsite);
                    foreach ($subsites as $sid) {
                        $vsite_filter->condition('og_group_ref', $vsite->group_type . ":" . $sid);
                    }
                }

                if (variable_get('os_search_solr_search_sites')) {
                    // Parse the list of 'other sites'.
                    foreach (variable_get('os_search_solr_search_sites', array()) as $sid) {
                        if (intval($sid)) {
                            $vsite_filter->condition('og_group_ref', $vsite->group_type . ":" . $sid);
                        }
                    }
                }
            }

            $query->filter($vsite_filter);

            $group_wrapper = entity_metadata_wrapper('node', $vsite->group);

            // The site is private but this user has access so let them see.
            if (
                !(module_exists('vsite_access') && !empty($group_wrapper->{VSITE_ACCESS_FIELD}) && $group_wrapper->{VSITE_ACCESS_FIELD}->value()) 
                ||
                !((user_access('administer group') || og_is_member('node', $group_wrapper->getIdentifier())))
            )
            {
                $query->condition('private', '1', '<>');
            }

            // Bundles which belong for disabled apps should appear in the search.
            if ($bundles = array_keys(os_get_bundles(array(OS_DISABLED_APP)))) {
                $f = $query->createFilter('OR');

                foreach( $bundles as $bundle ) {
                    $f->condition('bundle', $bundle, '<>');
                }

                $query->filter($f);
            }
        } elseif (variable_get('file_default_scheme', 'public') == 'private') {
            // We are not in a vsite, and we are in a private install.
            drupal_access_denied();
            drupal_exit();
        }

        try {
            $r = parent::search($query);
            return $r;
        } catch(Exception $e) {
            watchdog_exception("os_search", $e);
        }

        return [];
    }

    protected function addIndexField(SearchApiSolrDocument $doc, $key, $value, $type, $multi_valued = FALSE) {
        parent::addIndexField($doc, $key, $value, $type, $multi_valued);
    }

    public function alterSolrDocuments(array &$documents, SearchApiIndex $index, array $items)
    {
        $field_maps  = field_info_field_map();
        $entity_type = "node";
        
        foreach($documents as $i => &$document) {

            if (empty($items[$i])) {
                continue;
            }

            $item                  = $items[$i];
            $entity_id             = $item['nid']['value'];

            $entity                = node_load($entity_id);
            $emw                   = entity_metadata_wrapper('node', $entity);
            $document->entity_type = $entity_type;
            $document->entity_id   = $entity_id;
            $document->bundle      = $emw->getBundle();
            $document->bundle_name = self::entity_bundle_label($entity_type, $emw->getBundle());
            $document->bs_private  = !$this->privacy_callback($entity_type, $emw);

            $build = node_view($entity, 'search_index', $language);
            unset($build['#theme']);
            $build['#cache'] = true;
            $text = drupal_render($build);
            $document->content = self::clean_text($text);

            // Adding the teaser
            if (isset($entity->teaser)) {
                $document->teaser = self::clean_text($entity->teaser);
            } else {
                // If there is no node teaser we will have to generate the teaser
                // ourselves. We have to be careful to not leak the author and other
                // information that is normally also not visible.
                if (isset($entity->body[$language][0]['safe_summary'])) {
                    $document->teaser = self::clean_text($entity->body[$language][0]['safe_summary']);
                }
                else {
                    $document->teaser = truncate_utf8($document->content, 300, TRUE);
                }
            }

            $property_info   = $emw->getPropertyInfo();
            $item_field_maps = field_info_instances($entity_type, $entity->type);

            foreach( $item_field_maps as $name => $data ) {
                $field_info = $field_maps[$name];
                $info       = $property_info[$name];
                $field_type = explode( '<', str_replace('>', '', $info["type"]) );

                if ( $field_info["type"] == "entityreference" ) {

                    $item_entity_type = (count($field_type) > 1) ? $field_type[1] : $field_type[0];
                    $fields = [];
                    $ids    = [];

                    if ( $entity->{$name}[LANGUAGE_NONE] ) {
                        foreach ($entity->{$name}[LANGUAGE_NONE] as $reference) {
                            if ($id = (!empty($reference['target_id'])) ? $reference['target_id'] : FALSE) {
                                $fields[] = $item_entity_type . ':' . $id;
                                $ids[]    = $id;
                            }
                        }
                    }

                    if ( $item_entity_type == 'taxonomy_term' ) {
                        $terms  = entity_load($entity_type, $ids);
                        $fields = [];

                        foreach ($terms as $term) {
                            if ( empty($fields[$vid]) ) {
                                $fields[$vid] = [];
                            }
                            $fields[$term->vid][] = $term->name;
                        }

                        foreach ( $fields as $vid => $values ) {
                            $document->setField('tm_vid_' . $vid . '_names', $values);
                        }
                    }

                    $document->setField("sm_{$name}", $fields);
                }
            }
        }
    }

    public static function entity_bundle_label($entity_type, $bundle_name) {
        $labels = &drupal_static(__FUNCTION__, array());

        if (empty($labels)) {
            foreach (entity_get_info() as $type => $info) {
            foreach ($info['bundles'] as $bundle => $bundle_info) {
                $labels[$type][$bundle] = !empty($bundle_info['label']) ? $bundle_info['label'] : FALSE;
            }
            }
        }
        
        return $labels[$entity_type][$bundle_name];
    }

    private function privacy_callback($entity_type, $wrapper)
    {
        if (!module_exists('vsite')) {
            // We don't have groups.
            return true;
        }

        $bundle = $wrapper->getBundle();

        if ($entity_type != 'node' || !og_is_group_content_type($entity_type, $bundle)) {
            // Entity is not a node, or not a group content.
            return true;
        }

        $gids = $wrapper->{OG_AUDIENCE_FIELD}->value(array('identifier' => true));
        if (!count($gids)) {
            // Entity is not assigned to a group.
            return true;
        }
        $gid = current($gids);
        $group_wrapper = entity_metadata_wrapper('node', $gid);

        if (module_exists('vsite_access') && !empty($group_wrapper->{VSITE_ACCESS_FIELD}) && $group_wrapper->{VSITE_ACCESS_FIELD}->value()) {
            // Private group, and not a private install, exclude it.
            if (variable_get('file_default_scheme', 'public') != 'private') {
                return false;
            }
        }

        $map = features_get_component_map('node');
        if (!$feature = !empty($map[$bundle]) ? reset($map[$bundle]) : null) {
            return true;
        }

        $arguments = array(
            ':type' => 'og',
            ':id' => $gid,
            ':otype' => 'variable',
            ':oid' => 'spaces_features',
        );

        $result = db_query("SELECT value FROM {spaces_overrides} WHERE type = :type AND id = :id AND object_type = :otype AND object_id = :oid LIMIT 1", $arguments);

        foreach ($result as $row) {
            $features = unserialize($row->value);
            if (empty($features[$feature]) || $features[$feature] != 1) {
                // Disabled or private feature, flag it.
                return false;
            }
        }

        // If we reached this point, it means the node is 'public'.
        return true;
    }

    /**
     * Strip html tags and also control characters that cause Jetty/Solr to fail.
     */
    public static function clean_text($text) {
        // Remove invisible content.
        $text = preg_replace('@<(applet|audio|canvas|command|embed|iframe|map|menu|noembed|noframes|noscript|script|style|svg|video)[^>]*>.*</\1>@siU', ' ', $text);
        // Add spaces before stripping tags to avoid running words together.
        $text = filter_xss(str_replace(array('<', '>'), array(' <', '> '), $text), array());
        // Decode entities and then make safe any < or > characters.
        $text = htmlspecialchars(html_entity_decode($text, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        // Remove extra spaces.
        $text = preg_replace('/\s+/s', ' ', $text);
        // Remove white spaces around punctuation marks probably added
        // by the safety operations above. This is not a world wide perfect solution,
        // but a rough attempt for at least US and Western Europe.
        // Pc: Connector punctuation
        // Pd: Dash punctuation
        // Pe: Close punctuation
        // Pf: Final punctuation
        // Pi: Initial punctuation
        // Po: Other punctuation, including ¿?¡!,.:;
        // Ps: Open punctuation
        $text = preg_replace('/\s(\p{Pc}|\p{Pd}|\p{Pe}|\p{Pf}|!|\?|,|\.|:|;)/s', '$1', $text);
        $text = preg_replace('/(\p{Ps}|¿|¡)\s/s', '$1', $text);
        return $text;
    }
}
