<?php

class OsSearchApiSolrService extends SearchApiSolrService
{
    /**
     * {@inheritdoc}
     */
    public function search(SearchApiQueryInterface $query)
    {

        if (module_exists('vsite') && ($vsite = spaces_get_space())) {
            // This site.
            $sites = array('"' . $vsite->group_type . ":" . $vsite->id . '"');

            if (variable_get('os_search_solr_include_subsites') || variable_get('os_search_solr_search_sites')) {
                ctools_include('subsite', 'vsite');

                if (variable_get('os_search_solr_include_subsites')) {
                    // Get Subsites.
                    $subsites = vsite_get_subsites($vsite);
                    foreach ($subsites as $sid) {
                        $sites[] = '"' . $vsite->group_type . ":" . $sid . '"';
                    }
                }

                if (variable_get('os_search_solr_search_sites')) {
                    // Parse the list of 'other sites'.
                    foreach (variable_get('os_search_solr_search_sites', array()) as $sid) {
                        if (intval($sid)) {
                            $sites[] = '"' . $vsite->group_type . ":" . $sid . '"';
                        }
                    }
                }
            }

            // Filter to the specified sites.
            $site_filter = '(' . implode(' OR ', $sites) . ')';

            $f = $query->createFilter();
            $f->condition('sm_og_group_ref', $site_filter);
            //$query->filter($f);

            $group_wrapper = entity_metadata_wrapper('node', $vsite->group);

            // The site is private but this user has access so let them see.
            if (
                !(module_exists('vsite_access') && !empty($group_wrapper->{VSITE_ACCESS_FIELD}) && $group_wrapper->{VSITE_ACCESS_FIELD}->value()) 
                ||
                !((user_access('administer group') || og_is_member('node', $group_wrapper->getIdentifier())))
            )
            {
                $f = $query->createFilter();
                $f->condition('bs_private', '1', '<>');
                //$query->filter($f);    
            }

            // Bundles which belong for disabled apps should appear in the search.
            if ($bundles = array_keys(os_get_bundles(array(OS_DISABLED_APP)))) {
                $f = $query->createFilter();
                $f->condition('bundle', '(' . implode(' OR ', $bundles) . ')', '<>');
                $query->filter($f);
            }
        } elseif (variable_get('file_default_scheme', 'public') == 'private') {
            // We are not in a vsite, and we are in a private install.
            drupal_access_denied();
            drupal_exit();
        }

        if (!variable_get('os_search_solr_query_multisites', false)) {
            // Limit searches to just this OpenScholar install in shared indexes.
            //$query->addFilter('hash', apachesolr_site_hash());
        }

        return parent::search($query);
    }

    public function alterSolrDocuments(array &$documents, SearchApiIndex $index, array $items)
    {
        $path = drupal_get_path('module', 'os_search');

        file_put_contents($path . "/logs/index.log", print_r($index, true));

        for ($i = 0; $i < count($documents); $i++) {
            if (empty($items[$i])) {
                continue;
            }

            //$documents[$i]->entity_id = $items[$i]['nid']['value'];
            $documents[$i]->entity_type = "node";

            $documents[$i]->bs_private = $this->_isPrivate($items[$i]['nid']['value']);
            file_put_contents($path . "/logs/items[{$i}].log", print_r($items[$i], true));
            file_put_contents($path . "/logs/documents[{$i}].log", print_r($documents[$i], true));
        }
    }

    private function _isPrivate($entity_id)
    {
        $entity_type = "node";

        if (!module_exists('vsite')) {
            // We don't have groups.
            return true;
        }

        if (!$entity = entity_load_single($entity_type, $entity_id)) {
            // Entity can't be loaded.
            return false;
        }

        $wrapper = entity_metadata_wrapper($entity_type, $entity);
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
}
