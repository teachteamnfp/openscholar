<?php

// @codingStandardsIgnoreStart
use Robo\Exception\TaskException;

/**
 * Base tasks for setting up a module to test within a full Drupal environment.
 *
 * This file expects to be called from the root of a Drupal site.
 *
 * @class RoboFile
 * @codeCoverageIgnore
 */
class RoboFile extends \Robo\Tasks
{

    /**
     * The database URL.
     */
    const DB_URL = 'mysql://drupal:drupal@mariadb/drupal';

    /**
     * The website's URL.
     */
    const DRUPAL_URL = 'http://drupal.docker.localhost:8000';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        // Treat this command like bash -e and exit as soon as there's a failure.
        $this->stopOnFail();
    }

    /**
     * Command to run unit tests.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobRunUnitTests($groups = '')
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->enableXDebug());
        $collection->addTaskList($this->runUnitTests($groups));
        return $collection->run();
    }

    /**
     * Command to check coding standards.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobCheckCodingStandards()
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->runCheckCodingStandards());
        return $collection->run();
    }

  /**
   * Command to run kernel tests.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
    public function jobRunKernelTests($groups = '')
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->installDrupal());
        $collection->addTaskList($this->installTestConfigs());
        $collection->addTaskList($this->enableXDebug());
        $collection->addTaskList($this->runKernelTests($groups));
        return $collection->run();
    }

    /**
     * Command to run functional tests.
     *
     * @param string $groups
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobRunFunctionalTests($groups = '')
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->installDrupal());
        $collection->addTaskList($this->installTestConfigs());
        $collection->addTaskList($this->enableXDebug());
        $collection->addTaskList($this->runFunctionalTests($groups));
        return $collection->run();
    }

    /**
     * Command to run functional javascript tests (headless).
     *
     * @param string $groups
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobRunFunctionalJavascriptTests($groups = '')
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->installDrupal());
        $collection->addTaskList($this->installTestConfigs());
        $collection->addTaskList($this->enableXDebug());
        $collection->addTaskList($this->runFunctionalJavascriptTests($groups));
        return $collection->run();
    }

    /**
     * Command to run behat tests.
     *
     * @return \Robo\Result
     *   The result tof the collection of tasks.
     */
    public function jobRunBehatTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->downloadDatabase());
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTask($this->waitForDrupal());
        $collection->addTaskList($this->runUpdatePath());
        $collection->addTaskList($this->runBehatTests());
        return $collection->run();
    }

    /**
     * Download's database to use within a Docker environment.
     *
     * This task assumes that there is an environment variable that contains a URL
     * that contains a database dump. Ideally, you should set up drush site
     * aliases and then replace this task by a drush sql-sync one. See the
     * README at lullabot/drupal8ci for further details.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function downloadDatabase()
    {
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->mkdir('mariadb-init');
        $tasks[] = $this->taskExec('wget ' . getenv('DB_DUMP_URL'))
            ->dir('mariadb-init');
        return $tasks;
    }

    /**
     * Builds the Docker environment.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function buildEnvironment()
    {
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.travis/docker-compose.yml', 'docker-compose.yml', $force)
            ->copy('.travis/traefik.yml', 'traefik.yml', $force)
            ->copy('.travis/.env', '.env', $force)
            ->copy('.travis/config/behat.yml', 'tests/behat.yml', $force);

        $tasks[] = $this->taskExec('docker-compose pull --parallel');
        $tasks[] = $this->taskExec('docker-compose up -d');
        $tasks[] = $this->taskExec('docker-compose exec -T php composer install');
        $tasks[] = $this->taskExec('docker-compose exec -T php cp .travis/config/phpunit.xml web/core/phpunit.xml');
        $tasks[] = $this->taskExec('docker-compose exec -T php cp .travis/config/bootstrap.php web/core/tests/bootstrap.php');
        $tasks[] = $this->taskExec('docker-compose exec -T php mkdir web/sites/simpletest');
        return $tasks;
    }

    /**
     * Enables xdebug in the Docker environment.
     *
     * @return \Robo\Task\Base\Exec[]
     *   Array of tasks.
     */
    protected function enableXDebug()
    {
        $tasks[] = $this->taskExecStack()
            ->exec('echo PHP_XDEBUG_ENABLED=1 >> .env')
            ->exec('docker-compose up -d');

        return $tasks;
    }

    /**
     * Waits for Drupal to accept requests.
     *
     * @TODO Find an efficient way to wait for Drupal.
     *
     * @return \Robo\Task\Base\Exec
     *   A task to check that Drupal is ready.
     */
    protected function waitForDrupal()
    {
        return $this->taskExec('sleep 30s');
    }

    /**
     * Updates the database.
     *
     * We can't use the drush() method because this is running within a docker-compose
     * environment.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUpdatePath()
    {
        $tasks = [];
        $tasks[] = $this->taskExec('docker-compose exec -T php vendor/bin/drush --yes updatedb');
        $tasks[] = $this->taskExec('docker-compose exec -T php vendor/bin/drush --yes config-import');
        return $tasks;
    }

    /**
     * Install Drupal.
     *
     * @return \Robo\Task\Base\Exec[]
     *   A task to install Drupal.
     */
    protected function installDrupal()
    {
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php cp .travis/config/default.settings.php web/sites/default/default.settings.php')
            ->exec('docker-compose exec -T php ./vendor/bin/drush site-install openscholar -vvv -y --db-url=' . static::DB_URL . ' --existing-config');

        return $tasks;
    }

    /**
     * Install test configurations.
     *
     * @return \Robo\Task\Base\Exec[]
     *   A task to install Drupal.
     */
    protected function installTestConfigs()
    {
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php mkdir web/modules/test')
            ->exec('docker-compose exec -T php cp -r profile/modules/vsite/tests/modules/vsite_module_test web/modules/test')
            ->exec('docker-compose exec -T php cp -r web/modules/contrib/group/tests/modules/group_test_config web/modules/test')
            ->exec('docker-compose exec -T php cp -r profile/modules/custom/os_mailchimp/tests/modules/os_mailchimp_test web/modules/test')
            ->exec('docker-compose exec -T php ./vendor/bin/drush en -y vsite_module_test group_test_config os_mailchimp_test');
        return $tasks;
    }

    /**
     * Starts the web server.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function startWebServer()
    {
        $tasks = [];
        $tasks[] = $this->taskExec('vendor/bin/drush --root=' . $this->getDocroot() . '/web runserver ' . static::DRUPAL_URL . ' &')
            ->silent(true);
        $tasks[] = $this->taskExec('until curl -s ' . static::DRUPAL_URL . '; do true; done > /dev/null');
        return $tasks;
    }

    /**
     * Run coding standard checks.
     *
     * @return \Robo\Task\Base\Exec[]
     *   List of tasks.
     */
    protected function runCheckCodingStandards()
    {
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('docker-compose exec -T php ./vendor/bin/phpcs --config-set installed_paths vendor/drupal/coder/coder_sniffer')
            ->exec('docker-compose exec -T php ./vendor/bin/phpcs --standard=Drupal --warning-severity=0 profile')
            ->exec('docker-compose exec -T php ./vendor/bin/phpcs --standard=DrupalPractice --warning-severity=0 profile');

        return $tasks;
    }

    /**
     * Run unit tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUnitTests($groups)
    {
        $groups = explode(',', $groups);
        $groups = array_filter($groups, 'trim');
        $groups[] = 'unit';
        $groups = implode(',', $groups);
        $tasks[] = $this->taskExecStack()
          ->exec('docker-compose exec -T php ./vendor/bin/phpunit ' .
              '-c web/core '.
              '--debug '.
              ($groups ? '--group ' . $groups . ' ': ' ')  .
              '--exclude-group=kernel,functional,functional-javascript '.
              '--verbose web/profiles/contrib/openscholar');
        return $tasks;
    }

  /**
   * Run kernel tests.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
    protected function runKernelTests($groups)
    {
        $groups = explode(',', $groups);
        $groups = array_filter($groups, 'trim');  // strip out empty lines
        $groups[] = 'kernel';
        $groups = implode(',', $groups);
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php ./vendor/bin/phpunit ' .
                '-c web/core '.
                '--debug '.
                ($groups ? '--group ' . $groups . ' ': ' ')  .
                '--exclude-group=unit,functional,functional-javascript '.
                '--verbose web/profiles/contrib/openscholar');
        return $tasks;
    }

    /**
     * Run functional tests.
     *
     * @param string $groups
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runFunctionalTests($groups)
    {
        $groups = explode(',', $groups);
        $groups = array_filter($groups, 'trim');
        $groups[] = 'functional';
        $groups = implode(',', $groups);
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php ./vendor/bin/phpunit ' .
                '-c web/core '.
                '--debug '.
                ($groups ? '--group ' . $groups . ' ': ' ')  .
                '--exclude-group=unit,kernel,functional-javascript '.
                '--verbose web/profiles/contrib/openscholar');
        return $tasks;
    }

    /**
     * Run functional javascript tests (headless).
     *
     * @param string $groups
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runFunctionalJavascriptTests($groups)
    {
        $groups = explode(',', $groups);
        $groups = array_filter($groups, 'trim');
        $groups[] = 'functional-javascript';
        $groups = implode(',', $groups);
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php ./vendor/bin/phpunit ' .
                '-c web/core '.
                '--debug '.
                ($groups ? '--group ' . $groups . ' ': ' ')  .
                '--exclude-group=unit,kernel,functional '.
                '--verbose web/profiles/contrib/openscholar');
        return $tasks;
    }

    /**
     * Runs Behat tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runBehatTests()
    {
        $tasks = [];
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php vendor/bin/behat --verbose -c tests/behat.yml');
        return $tasks;
    }

    /**
     * Return drush with default arguments.
     *
     * @return \Robo\Task\Base\Exec
     *   A drush exec command.
     */
    protected function drush()
    {
        // Drush needs an absolute path to the docroot.
        $docroot = $this->getDocroot() . DIRECTORY_SEPARATOR. 'web';
        return $this->taskExec('vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'drush')
           ->option('root', $docroot, '=');
    }

    /**
     * Get the absolute path to the docroot.
     *
     * @return string
     *   The document root.
     */
    protected function getDocroot()
    {
        return (getcwd());
    }

}
