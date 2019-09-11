<?php

namespace Drupal\Tests\vsite_privacy\ExistingSite;

use Drupal\robotstxt\Controller\RobotsTxtController;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class RobotsTxtTest.
 *
 * @group kernel
 * @group vsite
 */
class RobotsTxtTest extends OsExistingSiteTestBase {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $group_admin = $this->createUser();

    $this->addGroupAdmin($group_admin, $this->group);
  }

  /**
   * Test group robots.txt if privacy is public.
   */
  public function testPublicPrivacySettings() {
    $this->group->set('field_privacy_level', 'public')->save();
    $this->vsiteContextManager->activateVsite($this->group);
    $robots_controller = new RobotsTxtController($this->container->get('config.factory'), $this->container->get('module_handler'));
    /** @var \Symfony\Component\HttpFoundation\Response $request */
    $request = $robots_controller->content();
    $this->assertEquals(200, $request->getStatusCode());
    $this->assertEquals('#
# robots.txt
#
# This file is to prevent the crawling and indexing of certain parts
# of your site by web crawlers and spiders run by sites like Yahoo!
# and Google. By telling these "robots" where not to go on your site,
# you save bandwidth and server resources.
#
# This file will be ignored unless it is at the root of your host:
# Used:    http://example.com/robots.txt
# Ignored: http://example.com/site/robots.txt
#
# For more information about the robots.txt standard, see:
# http://www.robotstxt.org/robotstxt.html

User-agent: *
# CSS, JS, Images
Allow: /core/*.css$
Allow: /core/*.css?
Allow: /core/*.js$
Allow: /core/*.js?
Allow: /core/*.gif
Allow: /core/*.jpg
Allow: /core/*.jpeg
Allow: /core/*.png
Allow: /core/*.svg
Allow: /profiles/*.css$
Allow: /profiles/*.css?
Allow: /profiles/*.js$
Allow: /profiles/*.js?
Allow: /profiles/*.gif
Allow: /profiles/*.jpg
Allow: /profiles/*.jpeg
Allow: /profiles/*.png
Allow: /profiles/*.svg
# Directories
Disallow: /core/
Disallow: /profiles/
# Files
Disallow: /README.txt
Disallow: /web.config
# Paths (clean URLs)
Disallow: /admin/
Disallow: /comment/reply/
Disallow: /filter/tips
Disallow: /node/add/
Disallow: /search/
Disallow: /user/register/
Disallow: /user/password/
Disallow: /user/login/
Disallow: /user/logout/
# Paths (no clean URLs)
Disallow: /index.php/admin/
Disallow: /index.php/comment/reply/
Disallow: /index.php/filter/tips
Disallow: /index.php/node/add/
Disallow: /index.php/search/
Disallow: /index.php/user/password/
Disallow: /index.php/user/register/
Disallow: /index.php/user/login/
Disallow: /index.php/user/logout/', $request->getContent());
  }

}
