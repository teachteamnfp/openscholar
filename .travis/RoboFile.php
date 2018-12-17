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
    const DB_URL = 'sqlite://sites/autotest/files/.ht.sqlite';

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
        $collection->addTaskList($this->runUnitTests($groups));
        return $collection->run();
    }

    /**
     * Command to check coding standards.
     *
     * @return null|\Robo\Result
     *   The result of the set of tasks.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function jobCheckCodingStandards()
    {
        return $this->taskExecStack()
            ->stopOnFail()
            ->exec('vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpcs --config-set installed_paths vendor'.DIRECTORY_SEPARATOR.'drupal'.DIRECTORY_SEPARATOR.'coder'.DIRECTORY_SEPARATOR.'coder_sniffer')
            ->exec('vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpcs --standard=Drupal --warning-severity=0 profile')
            ->exec('vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpcs --standard=DrupalPractice --warning-severity=0 profile')
            ->run();
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
        //$collection->addTask($this->installDrupal());
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
        $collection->addTask($this->installDrupal());
        $collection->addTaskList($this->runFunctionalTests($groups));
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
            ->copy('.travis/config/settings.local.php',
                'web/sites/default/settings.local.php', $force)
            ->copy('.travis/config/behat.yml', 'tests/behat.yml', $force);

        $tasks[] = $this->taskExec('docker-compose pull --parallel');
        $tasks[] = $this->taskExec('docker-compose up -d');
        $tasks[] = $this->taskExec('docker-compose exec php mkdir -p build');
        $tasks[] = $this->taskExec('docker-compose exec php chmod 777 build');
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
     * @return \Robo\Task\Base\Exec
     *   A task to install Drupal.
     */
    protected function installDrupal()
    {
        return $this->taskExec('docker-compose exec php sudo ./vendor/bin/drush site-install openscholar -vvv -y --db-url=' . static::DB_URL . ' --sites-subdir=autotest --existing-config');
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
     * Run unit tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUnitTests($groups)
    {
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.travis'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'phpunit.xml', 'web'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'phpunit.xml', $force);
        $tasks[] = $this->taskExecStack()
            ->dir('web')
            ->exec('..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpunit -c core --debug --coverage-clover ../build/logs/clover.xml '. ($groups ? '--group '.$groups.' ': ' ')  .'--exclude-group=kernel,functional --verbose profiles/contrib/openscholar');
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
      $force = true;
      $tasks = [];
      $tasks[] = $this->taskFilesystemStack()
        ->copy('.travis'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'phpunit.xml', 'web'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'phpunit.xml', $force);
      $tasks[] = $this->taskExecStack()
        ->dir('web')
        ->exec('..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpunit -c core --debug --coverage-clover ../build/logs/clover.xml '. ($groups ? '--group ' . $groups . ' ': ' ')  .'--exclude-group=unit,functional --verbose profiles/contrib/openscholar');
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
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.travis/config/phpunit.xml', 'web/core/phpunit.xml', TRUE)
            ->copy('.travis/config/bootstrap.php', 'web/core/tests/bootstrap.php', TRUE)
            ->mkdir('web/sites/simpletest');
        $tasks[] = $this->taskExecStack()
            ->exec('docker-compose exec -T php sudo ./vendor/bin/phpunit ' .
              '-c web/core '.
              '--debug '.
              '--coverage-clover build/logs/clover.xml '.
              ($groups ? '--group ' . $groups . ' ': ' ')  .
              '--exclude-group=unit,kernel '.
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
