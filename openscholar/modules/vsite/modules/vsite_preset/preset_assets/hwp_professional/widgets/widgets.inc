<?php

$widgets = [
  'site_info' => [
    'plugin' => 'os_boxes_site_info',
    'title' => '',
    'description' => t('Site Information'),
    'options' => [
      'site_title' => 'Alison Francis',
      'descrip' => 'Independent Consultant',
      'address1' => '',
      'address2' => '',
    ],
  ],
  'follow_me' => [
    'plugin' => 'os_boxes_follow',
    'title' => t('EDIT THIS WIDGET TO INCLUDE YOUR INFORMATION!'),
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
  'homepage_slideshow' => [
    'plugin' => 'os_slideshow_box',
    'title' => '',
    'description' => t('Home Page Slideshow'),
    'options' => [
      'effect' => 'fade',
      'timeout' => 5000,
      'speed' => 700,
      'random' => FALSE,
      'layout' => '16_9_overlay',
      'nodes' => [
        [
          'nid' => FALSE,
          'image' => 'https://thescholr.com/files/professional/files/alphabets-ancient-box-529926.jpg',
          'link' => '',
          'headline' => 'Awesome Headline',
          'description' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
          'alt' => 'writing',
          'title' => 'writing',
          'weight' => 0,
        ],
        [
          'nid' => FALSE,
          'image' => 'https://thescholr.com/files/professional/files/beach-clouds-dawn-977737.jpg',
          'link' => '',
          'headline' => 'Another awesome headline',
          'description' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
          'alt' => 'location',
          'title' => 'location',
          'weight' => 1,
        ],
        [
          'nid' => FALSE,
          'image' => 'https://thescholr.com/files/professional/files/background-book-pages-book-series-762686_1.jpg',
          'link' => 'https://professional.thescholr.com/',
          'headline' => 'Third Awesome Headline',
          'description' => "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium",
          'alt' => 'books',
          'title' => 'books',
          'weight' => 2,
        ],
      ],
    ],
  ],
  'blog_post_list' => [
    'plugin' => 'os_sv_list_box',
    'title' => '',
    'description' => t('Blog List of Post'),
    'options' => [
      'content_type' => 'blog',
      'display' => 'teaser',
      'layout' => 'list',
      'number_of_items' => 1,
      'sort_by' => 'sort_newest',
    ],
  ],
  'about_featured_post' => [
    'plugin' => 'os_boxes_manual_list',
    'title' => '',
    'description' => t('About Featured Post widget'),
    'options' => [
      'nodes' => [
        [
          'nid' => FALSE,
          'alias' => 'about',
          'title' => 'About',
          'weight' => 0,
        ],
      ],
      'style' => 'teasers',
      'random' => FALSE,
      'hide_title' => 1,
      'callout_styling' => 1,
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
        'value' => '<p><span><iframe src="https://player.vimeo.com/video/246737700?app_id=122963" width="238" height="148" frameborder="0" title="Understanding the Basic Page App and Creating a Basic Page from OpenScholar on Vimeo" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe></span></p>',
        'format' => 'full_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
  'page_vs_section_layouts_video' => [
    'plugin' => 'os_boxes_html',
    'title' => t('Video: Individual Page vs. Section Layout (01:49 min)'),
    'description' => t('OS: Individual Page Layout vs. Section Layout Video'),
    'options' => [
      'text' => [
        'value' => '<p><span><iframe src="https://player.vimeo.com/video/246737556?app_id=122963" width="238" height="148" frameborder="0" title="Individual Page Layout vs. Section Layout from OpenScholar on Vimeo" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe></span></p>',
        'format' => 'full_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
  'blog_help_docs' => [
    'plugin' => 'os_boxes_html',
    'title' => t('Learn More about the Blog!)'),
    'description' => t('Blog help docs'),
    'options' => [
      'text' => [
        'value' => '<p>The Blog App adds a blog section to your site.</p>
<p>Learn More about how to use the Blog App including:</p>
<ul>
  <li><a href="https://help.theopenscholar.com/blogs#blog-settings" title="">Blog Settings</a></li>
  <li><a href="https://help.theopenscholar.com/blogs#adding-the-blog-to-a-menu" title="">Adding a Blog link to a&nbsp;Menu</a></li>
  <li><a href="https://help.theopenscholar.com/blogs#creating-blog-entries" title="">Creating Blog Entries</a></li>
  <li><a href="https://help.theopenscholar.com/blogs#content-revisions" title="">Content Revisions</a></li>
  <li><a href="https://help.theopenscholar.com/blogs#importing-a-blog" title="">Importing a Blog</a></li>
  <li><a href="https://help.theopenscholar.com/blogs" title="">and more!&nbsp;</a></li>
</ul>',
        'format' => 'filtered_html',
      ],
      'format' => 1,
      'additional_classes' => '',
    ],
  ],
];
