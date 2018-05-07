<?php
/**
 * @file
 * vsite_preset.spaces.inc
 */

/**
 * Implements hook_spaces_presets().
 */
function vsite_preset_spaces_presets() {
  $export = array();

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_administrative';
  $spaces_presets->title = 'HWP Administrative';
  $spaces_presets->description = 'The Standard Adminstrative Department site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array(
      0 => 'department',
    ),
    'og_tasks' => array(
      0 => 'vsite_preset_section_about',
      1 => 'vsite_preset_section_news_&_events',
      2 => 'vsite_preset_section_resources',
      3 => 'vsite_preset_section_activities',
      4 => 'vsite_preset_section_services',
      5 => 'vsite_preset_menu_order',
    ),
    'variable' => array(
      'vsite_preset_menu_order' => array(
        'primary-menu' => array(
          'HOME' => -7,
          'Services' => -6,
          'Activities' => -5,
          'People' => -4,
          'Resources' => -3,
          'News & Events' => -2,
          'About' => -1,
        ),
      ),
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_news' => '1',
        'os_events' => '1',
        'os_profiles' => '1',
      ),
      'spaces_features_available' => array(
        0 => 'os_blog',
        1 => 'os_booklets',
        2 => 'os_classes',
        3 => 'os_events',
        4 => 'os_faq',
        5 => 'os_files_private',
        6 => 'os_image_gallery',
        7 => 'os_links',
        8 => 'os_news',
        9 => 'os_pages',
        10 => 'os_presentations',
        11 => 'os_profiles',
        12 => 'os_publications',
        13 => 'os_reader',
        14 => 'os_software',
        15 => 'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_classic',
      'os_breadcrumbs_show_breadcrumbs' => 1,
      'os_publications_filter_publication_types' => array(
        100 => true,
        101 => true,
        102 => true,
        103 => true,
        104 => true,
        105 => true,
        106 => true,
        107 => true,
        108 => true,
        109 => true,
        110 => true,
        111 => true,
        112 => true,
        113 => true,
        114 => true,
        115 => true,
        116 => true,
        117 => true,
        118 => true,
        119 => true,
        120 => true,
        121 => true,
        122 => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
        127 => true,
        128 => true,
        129 => true,
        130 => true,
        131 => true,
        132 => true,
        133 => true,
        134 => true,
        135 => true,
        1000 => true,
        1001 => true,
      ),
    ),
    'menus' => array(
      'primary-menu' => array(
        900 => array(
          'link_path' => '<front>',
          'link_title' => 'HOME',
          'options' => array(),
          'mlid' => 900,
          'p1' => 900,
          'depth' => 1,
          'router_path' => 'home',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
        902 => array(
          'link_path' => 'people',
          'link_title' => 'People',
          'options' => array(),
          'mlid' => 902,
          'p1' => 902,
          'depth' => 1,
          'router_path' => 'people',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'os-secondary-menu' => array(
            'module' => 'os',
            'delta' => 'secondary-menu',
            'region' => 'header_third',
            'weight' => '-11',
            'status' => 0,
            'title' => '',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'os_search_solr-solr_search_box' => array(
            'module' => 'os_search_solr',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Administrative');
  t('The Standard Adminstrative Department site.');
  $export['hwp_administrative'] = $spaces_presets;

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_lab_research_group';
  $spaces_presets->title = 'HWP Lab/Research Group';
  $spaces_presets->description = 'The Standard Lab and Research Group site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array(
      0 => 'project',
    ),
    'og_tasks' => array(
      0 => 'vsite_preset_section_research',
      1 => 'vsite_preset_section_resources',
      2 => 'vsite_preset_section_news_&_events',
      3 => 'vsite_preset_section_about',
      4 => 'vsite_preset_menu_order',
    ),
    'variable' => array(
      'vsite_preset_menu_order' => array(
        'primary-menu' => array(
          'HOME' => -7,
          'Research' => -6,
          'Publications' => -5,
          'People' => -4,
          'Resources' => -3,
          'News & Events' => -2,
          'About' => -1,
        ),
      ),
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_news' => '1',
        'os_events' => '1',
        'os_profiles' => '1',
        'os_publications' => '1',
      ),
      'spaces_features_available' => array(
        0 => 'os_blog',
        1 => 'os_booklets',
        2 => 'os_classes',
        3 => 'os_events',
        4 => 'os_faq',
        5 => 'os_files_private',
        6 => 'os_image_gallery',
        7 => 'os_links',
        8 => 'os_news',
        9 => 'os_pages',
        10 => 'os_presentations',
        11 => 'os_profiles',
        12 => 'os_publications',
        13 => 'os_reader',
        14 => 'os_software',
        15 => 'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_modern',
      'os_breadcrumbs_show_breadcrumbs' => 1,
      'os_publications_filter_publication_types' => array(
        100 => true,
        101 => true,
        102 => true,
        103 => true,
        104 => true,
        105 => true,
        106 => true,
        107 => true,
        108 => true,
        109 => true,
        110 => true,
        111 => true,
        112 => true,
        113 => true,
        114 => true,
        115 => true,
        116 => true,
        117 => true,
        118 => true,
        119 => true,
        120 => true,
        121 => true,
        122 => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
        127 => true,
        128 => true,
        129 => true,
        130 => true,
        131 => true,
        132 => true,
        133 => true,
        134 => true,
        135 => true,
        1000 => true,
        1001 => true,
      ),
    ),
    'menus' => array(
      'primary-menu' => array(
        900 => array(
          'link_path' => '<front>',
          'link_title' => 'HOME',
          'options' => array(),
          'mlid' => 900,
          'p1' => 900,
          'depth' => 1,
          'router_path' => 'home',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
        901 => array(
          'link_path' => 'publications',
          'link_title' => 'Publications',
          'options' => array(),
          'mlid' => 901,
          'p1' => 901,
          'depth' => 1,
          'router_path' => 'publications',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
        902 => array(
          'link_path' => 'people',
          'link_title' => 'People',
          'options' => array(),
          'mlid' => 902,
          'p1' => 902,
          'depth' => 1,
          'router_path' => 'people',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'os-secondary-menu' => array(
            'module' => 'os',
            'delta' => 'secondary-menu',
            'region' => 'header_third',
            'weight' => '-11',
            'status' => 0,
            'title' => '',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'os_search_solr-solr_search_box' => array(
            'module' => 'os_search_solr',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Lab/Research Group');
  t('The standard Lab and Research Group site.');
  $export['hwp_lab_research_group'] = $spaces_presets;

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_project';
  $spaces_presets->title = 'HWP Project';
  $spaces_presets->description = 'The Standard Project site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array(
      0 => 'project',
    ),
    'og_tasks' => array(
      0 => 'vsite_preset_section_overview',
      1 => 'vsite_preset_section_activities',
      2 => 'vsite_preset_section_resources',
      3 => 'vsite_preset_section_news_&_events',
      4 => 'vsite_preset_section_about',
      5 => 'vsite_preset_menu_order',
    ),
    'variable' => array(
      'vsite_preset_menu_order' => array(
        'primary-menu' => array(
          'HOME' => -7,
          'Overview' => -6,
          'Activities' => -5,
          'People' => -4,
          'Resources' => -3,
          'News & Events' => -2,
          'About' => -1,
        ),
      ),
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_news' => '1',
        'os_events' => '1',
        'os_profiles' => '1',
      ),
      'spaces_features_available' => array(
        0 => 'os_blog',
        1 => 'os_booklets',
        2 => 'os_classes',
        3 => 'os_events',
        4 => 'os_faq',
        5 => 'os_files_private',
        6 => 'os_image_gallery',
        7 => 'os_links',
        8 => 'os_news',
        9 => 'os_pages',
        10 => 'os_presentations',
        11 => 'os_profiles',
        12 => 'os_publications',
        13 => 'os_reader',
        14 => 'os_software',
        15 => 'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_modern',
      'os_breadcrumbs_show_breadcrumbs' => 1,
      'os_publications_filter_publication_types' => array(
        100 => true,
        101 => true,
        102 => true,
        103 => true,
        104 => true,
        105 => true,
        106 => true,
        107 => true,
        108 => true,
        109 => true,
        110 => true,
        111 => true,
        112 => true,
        113 => true,
        114 => true,
        115 => true,
        116 => true,
        117 => true,
        118 => true,
        119 => true,
        120 => true,
        121 => true,
        122 => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
        127 => true,
        128 => true,
        129 => true,
        130 => true,
        131 => true,
        132 => true,
        133 => true,
        134 => true,
        135 => true,
        1000 => true,
        1001 => true,
      ),
    ),
    'menus' => array(
      'primary-menu' => array(
        900 => array(
          'link_path' => '<front>',
          'link_title' => 'HOME',
          'options' => array(),
          'mlid' => 900,
          'p1' => 900,
          'depth' => 1,
          'router_path' => 'home',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
        902 => array(
          'link_path' => 'people',
          'link_title' => 'People',
          'options' => array(),
          'mlid' => 902,
          'p1' => 902,
          'depth' => 1,
          'router_path' => 'people',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'os-secondary-menu' => array(
            'module' => 'os',
            'delta' => 'secondary-menu',
            'region' => 'header_third',
            'weight' => '-11',
            'status' => 0,
            'title' => '',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'os_search_solr-solr_search_box' => array(
            'module' => 'os_search_solr',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Project');
  t('The Standard Project site.');
  $export['hwp_project'] = $spaces_presets;

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_personal';
  $spaces_presets->title = 'HWP Personal';
  $spaces_presets->description = 'The Standard Personal site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array('personal'),
      'og_tasks' => array(
        0 => 'vsite_preset_section_bio',
      ),
    'variable' => array(
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_publications' => '1',
        'os_classes' => '1',
        'os_biocv' => '0',
      ),
      'vsite_preset_menu_order' => array(
        'primary-menu' => array(
          'Bio' => -3,
          'Classes' => -2,
          'Publications' => -1,
        ),
      ),
      'spaces_features_available' => array(
        'os_blog',
        'os_booklets',
        'os_classes',
        'os_events',
        'os_faq',
        'os_files_private',
        'os_image_gallery',
        'os_links',
        'os_news',
        'os_pages',
        'os_presentations',
        'os_profiles',
        'os_publications',
        'os_reader',
        'os_software',
        'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_classic',
      'os_appearance_hwpi_classic_flavor' => 'classic_indigo',
      'os_breadcrumbs_show_breadcrumbs' => 1,
      'os_publications_filter_publication_types' => array(
        100 => true,
        101 => true,
        102 => true,
        103 => true,
        104 => true,
        105 => true,
        106 => true,
        107 => true,
        108 => true,
        109 => true,
        110 => true,
        111 => true,
        112 => true,
        113 => true,
        114 => true,
        115 => true,
        116 => true,
        117 => true,
        118 => true,
        119 => true,
        120 => true,
        121 => true,
        122 => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
        127 => true,
        128 => true,
        129 => true,
        130 => true,
        131 => true,
        132 => true,
        133 => true,
        134 => true,
        135 => true,
        1000 => true,
        1001 => true,
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'os-secondary-menu' => array(
            'module' => 'os',
            'delta' => 'secondary-menu',
            'region' => 'header_third',
            'weight' => '-11',
            'status' => 0,
            'title' => '',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_logo' => array(
            'module' => 'boxes',
            'delta' => 'site_logo',
            'region' => 'header_first',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'os_search_solr-solr_search_box' => array(
            'module' => 'os_search_solr',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-hwp_personal_contact_html' => array(
            'module' => 'boxes',
            'delta' => 'hwp_personal_contact_html',
            'region' => 'sidebar_second',
            'weight' => '-10',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Personal');
  t('The Standard Personal site.');
  $export['hwp_personal'] = $spaces_presets;

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_professional';
  $spaces_presets->title = 'HWP Professional';
  $spaces_presets->description = 'The Professional site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array(
      0 => 'personal',
    ),
    'og_tasks' => array(
      0 => 'vsite_preset_import_content',
      1 => 'vsite_preset_import_menu',
      2 => 'vsite_preset_create_widgets',
      3 => 'vsite_preset_update_page_contexts',
    ),
    'variable' => array(
      'vsite_preset_import_content_types' => array(
        'page',
        'blog',
      ),
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_blog' => '1',
      ),
      'spaces_features_available' => array(
        0 => 'os_blog',
        1 => 'os_booklets',
        2 => 'os_classes',
        3 => 'os_events',
        4 => 'os_faq',
        5 => 'os_files_private',
        6 => 'os_image_gallery',
        7 => 'os_links',
        8 => 'os_news',
        9 => 'os_pages',
        10 => 'os_presentations',
        11 => 'os_profiles',
        12 => 'os_publications',
        13 => 'os_reader',
        14 => 'os_software',
        15 => 'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_classic',
      'os_appearance_hwpi_classic_flavor' => 'classic_indigo',
      'os_breadcrumbs_show_breadcrumbs' => 1,
      'show_email' => 0,
    ),
    'menus' => array(
      'primary-menu' => array(
        900 => array(
          'link_path' => '<front>',
          'link_title' => 'HOME',
          'options' => array(),
          'mlid' => 900,
          'p1' => 900,
          'depth' => 1,
          'router_path' => 'home',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
          ),
          'os_search_db-site-search' => array(
            'module' => 'os_search_db',
            'delta' => 'site-search',
            'region' => 'header_third',
            'weight' => '-10',
          ),
          'boxes-solr_search_box' => array(
            'module' => 'boxes',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
          ),
          'boxes-follow_me' => array(
            'module' => 'boxes',
            'delta' => 'follow_me',
            'region' => 'footer',
            'weight' => '-10',
          ),
          'boxes-contact-hwp_professional' => array(
            'module' => 'boxes',
            'delta' => 'contact',
            'region' => 'footer_third',
            'weight' => '-10',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(
          'boxes-homepage_slideshow' => array(
            'module' => 'boxes',
            'delta' => 'homepage_slideshow',
            'region' => 'content_top',
            'weight' => '-10',
            'title' => '',
          ),
          'boxes-blog_post_list' => array(
            'module' => 'boxes',
            'delta' => 'blog_post_list',
            'region' => 'content_top',
            'weight' => '0',
            'title' => '',
          ),
          'boxes-about_featured_post' => array(
            'module' => 'boxes',
            'delta' => 'about_featured_post',
            'region' => 'sidebar_second',
            'weight' => '-10',
            'title' => '',
          ),
        ),
      ),
      'os_pages-page-work:reaction:block' => array(
        'blocks' => array(
          'os_pages-main_content' => array(
            'module' => 'os_pages',
            'delta' => 'main_content',
            'region' => 'content_top',
            'weight' => '-10',
          ),
          'boxes-basic_page_app_links' => array(
            'module' => 'boxes',
            'delta' => 'basic_page_app_links',
            'region' => 'sidebar_second',
            'weight' => '-10',
          ),
          'boxes-basic_page_app_video' => array(
            'module' => 'boxes',
            'delta' => 'basic_page_app_video',
            'region' => 'sidebar_second',
            'weight' => '-9',
          ),
        ),
      ),
      'blog_blog:reaction:block' => array(
        'blocks' => array(
          'boxes-os_addthis' => array(
            'module' => 'boxes',
            'delta' => 'os_addthis',
            'region' => 'sidebar_second',
            'weight' => '-9',
          ),
          'views-os_blog-block' => array(
            'module' => 'views',
            'delta' => 'os_blog-block',
            'region' => 'sidebar_second',
            'weight' => '-8',
          ),
          'views-os_blog_comments-block' => array(
            'module' => 'views',
            'delta' => 'os_blog_comments-block',
            'region' => 'sidebar_second',
            'weight' => '-7',
          ),
          'boxes-blog_rss_feed' => array(
            'module' => 'boxes',
            'delta' => 'blog_rss_feed',
            'region' => 'footer',
            'weight' => '-10',
          ),
          'boxes-blog_help_docs' => array(
            'module' => 'boxes',
            'delta' => 'blog_help_docs',
            'region' => 'sidebar_second',
            'weight' => '1',
          ),
        ),
      ),
      'os_pages-section-about:reaction:block' => array(
        'blocks' => array(
          'boxes-os_pages_section_nav' => array(
            'module' => 'boxes',
            'delta' => 'os_pages_section_nav',
            'region' => 'sidebar_first',
            'weight' => '-10',
          ),
          'boxes-basic_page_app_links' => array(
            'module' => 'boxes',
            'delta' => 'basic_page_app_links',
            'region' => 'sidebar_first',
            'weight' => '1',
          ),
          'boxes-basic_page_app_video' => array(
            'module' => 'boxes',
            'delta' => 'basic_page_app_video',
            'region' => 'sidebar_first',
            'weight' => '2',
          ),
          'boxes-page_vs_section_layouts_video' => array(
            'module' => 'boxes',
            'delta' => 'page_vs_section_layouts_video',
            'region' => 'sidebar_first',
            'weight' => '3',
          ),
          'os_pages-main_content' => array(
            'module' => 'os_pages',
            'delta' => 'main_content',
            'region' => 'content_top',
            'weight' => '-10',
          ),
        ),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Professional');
  t('The Professional site.');
  $export['hwp_professional'] = $spaces_presets;

  $spaces_presets = new stdClass();
  $spaces_presets->disabled = FALSE; /* Edit this to true to make a default spaces_presets disabled initially */
  $spaces_presets->api_version = 3;
  $spaces_presets->name = 'hwp_event_conference';
  $spaces_presets->title = 'HWP Event/Conference';
  $spaces_presets->description = 'The Standard Event or Conference site.';
  $spaces_presets->space_type = 'og';
  $spaces_presets->value = array(
    'bundles' => array(
      0 => 'project',
    ),
    'og_tasks' => array(
      0 => 'vsite_preset_section_about',
      1 => 'vsite_preset_section_program',
      3 => 'vsite_preset_section_register',
      4 => 'vsite_preset_section_logistics',
      5 => 'vsite_preset_menu_order',
    ),
    'variable' => array(
      'vsite_preset_menu_order' => array(
        'primary-menu' => array(
          'HOME' => -6,
          'Program' => -5,
          'Speakers' => -4,
          'Register' => -3,
          'Logistics' => -2,
          'About' => -1,
        ),
      ),
      'spaces_features' => array(
        'os' => '1',
        'os_pages' => '1',
        'os_news' => '1',
        'os_events' => '1',
        'os_profiles' => '1',
      ),
      'spaces_features_available' => array(
        0 => 'os_blog',
        1 => 'os_booklets',
        2 => 'os_classes',
        3 => 'os_events',
        4 => 'os_faq',
        5 => 'os_files_private',
        6 => 'os_image_gallery',
        7 => 'os_links',
        8 => 'os_news',
        9 => 'os_pages',
        10 => 'os_presentations',
        11 => 'os_profiles',
        12 => 'os_publications',
        13 => 'os_reader',
        14 => 'os_software',
        15 => 'harvard_courses',
      ),
      'site_frontpage' => 'home',
      'theme_default' => 'hwpi_classic',
      'os_breadcrumbs_show_breadcrumbs' => 0,
      'os_publications_filter_publication_types' => array(
        100 => true,
        101 => true,
        102 => true,
        103 => true,
        104 => true,
        105 => true,
        106 => true,
        107 => true,
        108 => true,
        109 => true,
        110 => true,
        111 => true,
        112 => true,
        113 => true,
        114 => true,
        115 => true,
        116 => true,
        117 => true,
        118 => true,
        119 => true,
        120 => true,
        121 => true,
        122 => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
        127 => true,
        128 => true,
        129 => true,
        130 => true,
        131 => true,
        132 => true,
        133 => true,
        134 => true,
        135 => true,
        1000 => true,
        1001 => true,
      ),
    ),
    'menus' => array(
      'primary-menu' => array(
        900 => array(
          'link_path' => '<front>',
          'link_title' => 'HOME',
          'options' => array(),
          'mlid' => 900,
          'p1' => 900,
          'depth' => 1,
          'router_path' => 'home',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
        902 => array(
          'link_path' => 'people',
          'link_title' => 'Speakers',
          'options' => array(),
          'mlid' => 902,
          'p1' => 902,
          'depth' => 1,
          'router_path' => 'people',
          'menu_name' => 'primary-menu',
          'external' => 1,
          'weight' => 0,
          'hidden' => 0,
          'has_children' => 0,
          'expanded' => 0,
          'module' => 'menu',
          'customized' => 0,
          'updated' => 0,
          'plid' => 0,
          'p2' => 0,
          'p3' => 0,
          'p4' => 0,
          'p5' => 0,
          'p6' => 0,
          'p7' => 0,
          'p8' => 0,
          'p9' => 0,
        ),
      ),
    ),
    'context' => array(
      'os_public:reaction:block' => array(
        'blocks' => array(
          'os-secondary-menu' => array(
            'module' => 'os',
            'delta' => 'secondary-menu',
            'region' => 'header_third',
            'weight' => '-11',
            'status' => 0,
            'title' => '',
          ),
          'os-primary-menu' => array(
            'module' => 'os',
            'delta' => 'primary-menu',
            'region' => 'menu_bar',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'boxes-site_info' => array(
            'module' => 'boxes',
            'delta' => 'site_info',
            'region' => 'header_second',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
          'os_search_solr-solr_search_box' => array(
            'module' => 'os_search_solr',
            'delta' => 'solr_search_box',
            'region' => 'header_third',
            'weight' => '-10',
            'status' => 0,
            'title' => '',
          ),
        ),
      ),
      'os_front:reaction:block' => array(
        'blocks' => array(),
      ),
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('HWP Event/Conference');
  t('The Standard Event or Conference site.');
  t('Speakers');
  t('Program');
  t('Schedule');
  t('Register');
  t('Logistics');
  $export['hwp_event_conference'] = $spaces_presets;

  return $export;
}
