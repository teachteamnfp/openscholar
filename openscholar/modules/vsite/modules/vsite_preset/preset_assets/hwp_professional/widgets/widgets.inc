<?php

$widgets = [
  'follow_me' => [
    'plugin' => 'os_boxes_follow',
    'title' => t('Follow Me'),
    'description' => t('Follow Me'),
    'options' => [
      'links' => [
        [
          'title' => 'https://www.linkedin.com/company/openscholar-llc/',
          'domain' => 'linkedin',
          'weight' => 0,
        ],
        [
          'title' => 'https://twitter.com/TheOpenScholar',
          'domain' => 'twitter',
          'weight' => 1,
        ],
      ],
      'use_rss' => 0,
      'display_service_name' => 0,
    ],
  ],
  'contact' => [
    'plugin' => 'os_boxes_html',
    'title' => t('Contact'),
    'description' => t('Contact'),
    'options' => [
      'text' => [
        'value' => '<p>Your Name<br>(800) 555-0000<br>Your Town, State 00000<br><a href="mailto:your_name@your_school.edu">your_name@your_school.edu</a></p>',
        'format' => 'filtered_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
  'basic_page_app_links' => [
    'plugin' => 'os_boxes_html',
    'title' => t('Learn about Basic Pages & Subpages'),
    'description' => t('OS: Basic Page App links'),
    'options' => [
      'text' => [
        'value' => '<p>Helpful Documentation:</p><ul><li><a href="https://help.theopenscholar.com/create-pages-subpages" title="\">Overview of the Basic Page App</a></li><li><a href="https://help.theopenscholar.com/create-pages-subpages#creating-subpages" title="\">Creating Subpages</a>
	</li><li><a href="https://help.theopenscholar.com/section-navigation-widget" title="\">Understanding the Section Navigation widget</a></li></ul>',
        'format' => 'filtered_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
  'basic_page_app_video' => [
    'plugin' => 'os_boxes_html',
    'title' => t('Video - Basic Page App & Creating a Basic Page (01:42 min)'),
    'description' => t('OS: Basic Page App and Creating a Basic Page Video'),
    'options' => [
      'text' => [
        'value' => 'https://player.vimeo.com/video/246737700',
        'format' => 'filtered_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
];