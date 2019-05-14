<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\file\Entity\File;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_publications\Traits\OsPublicationsTestTrait;
use Drupal\views\Plugin\views\field\EntityField;
use Drupal\views\ViewExecutable;

/**
 * TestBase for bibcite customizations.
 */
abstract class TestBase extends OsExistingSiteTestBase {

  use OsPublicationsTestTrait;

  /**
   * Default repec settings.
   *
   * @var array
   */
  protected $defaultRepecSettings;

  /**
   * Repec service.
   *
   * @var \Drupal\repec\Repec
   */
  protected $repec;

  /**
   * Config service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->repec = $this->container->get('repec');
    $this->defaultRepecSettings = $this->configFactory->get('repec.settings')->getRawData();
    $this->repec->initializeTemplates();
  }

  /**
   * Asserts template content of a reference.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity. This is used as the expected data.
   * @param string $content
   *   The actual content.
   */
  protected function assertTemplateContent(ReferenceInterface $reference, $content): void {
    $this->assertStringStartsWith('Template-Type', $content);
    $this->assertContains("Title: {$reference->label()}", $content);
    $this->assertContains("Number: {$reference->uuid()}", $content);
    $this->assertContains("Handle: RePEc:{$this->defaultRepecSettings['archive_code']}:{$this->repec->getEntityBundleSettings('serie_type', $reference->getEntityTypeId(), $reference->bundle())}:{$reference->id()}", $content);

    $created_date = date('Y-m-d', $reference->getCreatedTime());
    $this->assertContains("Creation-Date: {$created_date}", $content);

    // Assert keywords.
    $keyword_names = [];
    foreach ($reference->get('keywords') as $item) {
      $keyword = Keyword::load($item->getValue()['target_id']);
      $keyword_names[] = $keyword->getName();
    }

    if ($keyword_names) {
      $keyword_names_in_template = implode(', ', $keyword_names);
      $this->assertContains("Keywords: {$keyword_names_in_template}", $content);
    }

    // Assert files.
    $files_data = [];
    foreach ($reference->get('field_files') as $item) {
      $file = File::load($item->getValue()['target_id']);
      $files_data[] = [
        'url' => file_create_url($file->getFileUri()),
        'type' => ucfirst($file->getMimeType()),
      ];
    }

    foreach ($files_data as $datum) {
      $this->assertContains("File-URL: {$datum['url']}", $content);
      $this->assertContains("File-Format: {$datum['type']}", $content);
    }

    // Assert authors.
    foreach ($reference->get('author') as $item) {
      $contributor = Contributor::load($item->getValue()['target_id']);
      $this->assertContains("Author-Name: {$contributor->getName()}", $content);
    }

    /** @var array $abstract */
    $abstract = $reference->get('bibcite_abst_e')->getValue();
    if ($abstract) {
      $this->assertContains("Abstract: {$abstract[0]['value']}", $content);
    }
  }

  /**
   * Orders a nested array containing a result set based on a given column.
   *
   * Copied from ViewsKernelTestBase::orderResultSet.
   *
   * @param array $result_set
   *   An array of rows from a result set, with each row as an associative
   *   array keyed by column name.
   * @param string $column
   *   The column name by which to sort the result set.
   * @param bool $reverse
   *   (optional) Boolean indicating whether to sort the result set in reverse
   *   order. Defaults to FALSE.
   *
   * @see \Drupal\Tests\views\Kernel\ViewsKernelTestBase::orderResultSet
   */
  protected function orderResultSet(array &$result_set, $column, $reverse = FALSE) {
    $order = $reverse ? -1 : 1;
    usort($result_set, function ($a, $b) use ($column, $order) {
      if ($a[$column] === $b[$column]) {
        return 0;
      }
      return $order * (($a[$column] < $b[$column]) ? -1 : 1);
    });
  }

  /**
   * Verifies that a result set returned by a View matches expected values.
   *
   * The comparison is done on the string representation of the columns of the
   * column map, taking the order of the rows into account, but not the order
   * of the columns.
   *
   * Copied from ViewResultAssertionTrait::assertIdenticalResultset.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   An executed View.
   * @param array $expected_result
   *   An expected result set.
   * @param array $column_map
   *   (optional) An associative array mapping the columns of the result set
   *   from the view (as keys) and the expected result set (as values).
   * @param string $message
   *   (optional) A custom message to display with the assertion. Defaults to
   *   'Identical result set.'.
   *
   * @see \Drupal\views\Tests\ViewResultAssertionTrait::assertIdenticalResultset
   */
  protected function assertIdenticalResultset(ViewExecutable $view, array $expected_result, array $column_map = [], $message = NULL) {
    // Convert $view->result to an array of arrays.
    $result = [];
    foreach ($view->result as $key => $value) {
      $row = [];
      foreach ($column_map as $view_column => $expected_column) {
        if (property_exists($value, $view_column)) {
          $row[$expected_column] = (string) $value->$view_column->label();
        }
        // For entity fields we don't have the raw value. Let's try to fetch it
        // using the entity itself.
        elseif (empty($value->$view_column) && isset($view->field[$expected_column]) && ($field = $view->field[$expected_column]) && $field instanceof EntityField) {
          $column = NULL;
          if (count(explode(':', $view_column)) == 2) {
            $column = explode(':', $view_column)[1];
          }
          // The comparison will be done on the string representation of the
          // value.
          $field_value = $field->getValue($value, $column);
          $row[$expected_column] = is_array($field_value) ? array_map('strval', $field_value) : (string) $field_value;
        }
      }
      $result[$key] = $row;
    }

    // Remove the columns we don't need from the expected result.
    foreach ($expected_result as $key => $value) {
      $row = [];
      foreach ($column_map as $expected_column) {
        // The comparison will be done on the string representation of the
        // value.
        if (is_object($value)) {
          $row[$expected_column] = (string) $value->$expected_column;
        }
        // This case is about fields with multiple values.
        elseif (is_array($value[$expected_column])) {
          foreach (array_keys($value[$expected_column]) as $delta) {
            $row[$expected_column][$delta] = (string) $value[$expected_column][$delta];
          }
        }
        else {
          $row[$expected_column] = (string) $value[$expected_column];
        }
      }
      $expected_result[$key] = $row;
    }

    $this->verbose('<pre style="white-space: pre-wrap;">'
      . "\n\nQuery:\n" . $view->build_info['query']
      . "\n\nQuery arguments:\n" . var_export($view->build_info['query']->getArguments(), TRUE)
      . "\n\nActual result:\n" . var_export($result, TRUE)
      . "\n\nExpected result:\n" . var_export($expected_result, TRUE));

    // Reset the numbering of the arrays.
    $result = array_values($result);
    $expected_result = array_values($expected_result);

    // Do the actual comparison.
    if (!isset($message)) {
      $message = new FormattableMarkup("Actual result <pre>\n@actual\n</pre> is not identical to expected <pre>\n@expected\n</pre>", [
        '@actual' => var_export($result, TRUE),
        '@expected' => var_export($expected_result, TRUE),
      ]);
    }
    return $this->assertSame($result, $expected_result, $message);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $template_path = "{$this->repec->getArchiveDirectory()}/{$this->defaultRepecSettings['archive_code']}seri.rdf";
    $real_path = $file_system->realpath($template_path);

    if (file_exists($real_path)) {
      unlink($real_path);
    }

    parent::tearDown();
  }

}
