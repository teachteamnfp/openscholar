<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\views\Plugin\views\field\EntityField;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Tests publication views.
 *
 * @group kernel
 */
class PublicationsViewsTest extends TestBase {

  /**
   * Default filter publication type settings.
   *
   * @var array
   */
  protected $defaultFilterPublicationTypeSettings;

  /**
   * Default publications sort order.
   *
   * @var string
   */
  protected $defaultSortOrder;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\ImmutableConfig $publications_settings */
    $publications_settings = $this->configFactory->get('os_publications.settings');
    $this->defaultFilterPublicationTypeSettings = $publications_settings->get('filter_publication_types');
    $this->defaultSortOrder = $publications_settings->get('biblio_order');
  }

  /**
   * Tests type display.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testType() {
    $this->createReference([
      'title' => 'Mona Lisa',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'The Rust Programming Language',
      'type' => 'journal',
      'bibcite_year' => [
        'value' => 2010,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_1');

    $this->assertCount(2, $result);

    $grouped_result = [];

    foreach ($result as $item) {
      $grouped_result[$item->_entity->bundle()][] = $item->_entity->label();
    }

    $this->assertCount(1, $grouped_result['artwork']);
    $this->assertCount(1, $grouped_result['journal']);
  }

  /**
   * Tests title display.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testTitle() {
    $this->createReference([
      'title' => 'The Last Supper',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'Girl with a Pearl Earring',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'Mona Lisa',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'Las Meninas',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_2');

    $this->assertCount(4, $result);

    $grouped_result = [];
    /** @var \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper */
    $publications_listing_helper = $this->container->get('os_publications.listing_helper');

    foreach ($result as $item) {
      $grouped_result[$publications_listing_helper->convertLabel($item->_entity->label())][] = $item->_entity->id();
    }

    $this->assertCount(1, $grouped_result['G']);
    $this->assertCount(1, $grouped_result['M']);
    $this->assertCount(2, $grouped_result['L']);
  }

  /**
   * Tests author display.
   *
   * @coversDefaultClass \Drupal\os_publications\Plugin\views\field\AuthorLastNameFirstLetter
   * @coversDefaultClass \Drupal\os_publications\Plugin\views\sort\AuthorLastNameFirstLetter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAuthor() {
    /** @var \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper */
    $publications_listing_helper = $this->container->get('os_publications.listing_helper');

    $contributor1 = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => 'Da',
      'last_name' => 'Vinci',
    ]);

    $contributor2 = $this->createContributor([
      'first_name' => 'Joanne',
      'middle_name' => 'Kathleen',
      'last_name' => 'Rowling',
    ]);

    $reference1 = $this->createReference([
      'title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor1->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference2 = $this->createReference([
      'title' => 'The Last Supper',
      'author' => [
        'target_id' => $contributor1->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference3 = $this->createReference([
      'title' => 'Harry Potter and the Deathly Hallows',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor2->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Bloomsbury',
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference4 = $this->createReference([
      'title' => 'Unknown',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $dataset = [
      [
        'title' => $reference1->label(),
        'author_last_name' => $publications_listing_helper->convertAuthorName($reference1),
      ],
      [
        'title' => $reference2->label(),
        'author_last_name' => $publications_listing_helper->convertAuthorName($reference2),
      ],
      [
        'title' => $reference3->label(),
        'author_last_name' => $publications_listing_helper->convertAuthorName($reference3),
      ],
      [
        'title' => $reference4->label(),
        'author_last_name' => $publications_listing_helper->convertAuthorName($reference4),
      ],
    ];

    $view = Views::getView('publications');
    $view->setDisplay('page_3');
    $view->preExecute();
    $view->execute();

    /** @var array $result */
    $result = $view->result;

    // Assert sorting by "first letter of author's last name".
    $ordered_dataset = $this->orderResultSet($dataset, 'author_last_name', TRUE);

    $this->assertCount(4, $result);
    $this->assertIdenticalResultset($view, $ordered_dataset, [
      '_entity' => 'title',
    ]);

    // Assert result grouping.
    $grouped_result = [];

    foreach ($result as $item) {
      /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $reference */
      $reference = $item->_entity;
      $grouped_result[$publications_listing_helper->convertAuthorName($reference)][] = $reference->id();
    }

    $this->assertCount(2, $grouped_result['V']);
    $this->assertCount(1, $grouped_result['R']);
    $this->assertCount(1, $grouped_result['']);
  }

  /**
   * Tests year display.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testYear() {
    $this->createReference([
      'title' => 'Girl with a Pearl Earring',
      'bibcite_year' => [
        'value' => 1665,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'The Persistence of Memory',
      'bibcite_year' => [
        'value' => 1931,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'The Starry Night',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $this->createReference([
      'title' => 'Foobar',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_4');

    $this->assertCount(4, $result);

    $grouped_result = [];

    foreach ($result as $item) {
      $grouped_result[$item->_entity->get('bibcite_year')->first()->getValue()['value']][] = $item->_entity->id();
    }

    $this->assertCount(1, $grouped_result['1665']);
    $this->assertCount(1, $grouped_result['1931']);
    $this->assertCount(2, $grouped_result['1889']);
  }

  /**
   * Tests whether publication types are filtered as per the settings.
   *
   * @covers ::os_publications_views_query_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testReferenceFilter() {
    $this->createReference([
      'title' => 'Girl with a Pearl Earring',
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    /** @var array $publications_by_type */
    $publications_by_type = views_get_view_result('publications', 'page_1');
    /** @var array $publications_by_title */
    $publications_by_title = views_get_view_result('publications', 'page_2');
    /** @var array $publications_by_author */
    $publications_by_author = views_get_view_result('publications', 'page_3');
    /** @var array $publications_by_year */
    $publications_by_year = views_get_view_result('publications', 'page_4');

    $this->assertCount(1, $publications_by_type);
    $this->assertCount(1, $publications_by_title);
    $this->assertCount(1, $publications_by_author);
    $this->assertCount(1, $publications_by_year);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $os_publications_settings_mut */
    $os_publications_settings_mut = $config_factory->getEditable('os_publications.settings');
    /** @var array $filter_publication_types */
    $filter_publication_types = $os_publications_settings_mut->get('filter_publication_types');
    $os_publications_settings_mut->set('filter_publication_types', [
      'artwork' => 0,
    ] + $filter_publication_types);
    $os_publications_settings_mut->save();

    /** @var array $publications_by_type */
    $publications_by_type = views_get_view_result('publications', 'page_1');
    /** @var array $publications_by_title */
    $publications_by_title = views_get_view_result('publications', 'page_2');
    /** @var array $publications_by_author */
    $publications_by_author = views_get_view_result('publications', 'page_3');
    /** @var array $publications_by_year */
    $publications_by_year = views_get_view_result('publications', 'page_4');

    $this->assertCount(0, $publications_by_type);
    $this->assertCount(0, $publications_by_title);
    $this->assertCount(0, $publications_by_author);
    $this->assertCount(0, $publications_by_year);
  }

  /**
   * Tests publication sort setting.
   *
   * @covers ::os_publications_views_query_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testSortOrder() {
    $reference1 = $this->createReference([
      'title' => 'Girl with a Pearl Earring',
      'bibcite_year' => [
        'value' => 1665,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference2 = $this->createReference([
      'title' => 'The Persistence of Memory',
      'bibcite_year' => [
        'value' => 1931,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference3 = $this->createReference([
      'title' => 'The Starry Night',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $reference4 = $this->createReference([
      'title' => 'Foobar',
      'bibcite_year' => [
        'value' => 1889,
      ],
      'field_is_sticky' => [
        'value' => 0,
      ],
    ]);

    $dataset = [
      [
        'title' => $reference1->label(),
        'year' => $reference1->get('bibcite_year')->first()->getValue()['value'],
      ],
      [
        'title' => $reference2->label(),
        'year' => $reference2->get('bibcite_year')->first()->getValue()['value'],
      ],
      [
        'title' => $reference3->label(),
        'year' => $reference3->get('bibcite_year')->first()->getValue()['value'],
      ],
      [
        'title' => $reference4->label(),
        'year' => $reference4->get('bibcite_year')->first()->getValue()['value'],
      ],
    ];

    $view = Views::getView('publications');
    $view->setDisplay('page_4');
    $view->preExecute();
    $view->execute();

    /** @var array $result */
    $result = $view->result;

    $ordered_dataset = $this->orderResultSet($dataset, 'year', TRUE);

    $this->assertCount(4, $result);
    $this->assertIdenticalResultset($view, $ordered_dataset, [
      '_entity' => 'title',
    ]);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $os_publications_settings_mut */
    $os_publications_settings_mut = $config_factory->getEditable('os_publications.settings');
    $os_publications_settings_mut->set('biblio_order', 'ASC');
    $os_publications_settings_mut->save();

    $view = Views::getView('publications');
    $view->setDisplay('page_4');
    $view->preExecute();
    $view->execute();

    /** @var array $result */
    $result = $view->result;

    $ordered_dataset = $this->orderResultSet($dataset, 'year');

    $this->assertCount(4, $result);
    $this->assertIdenticalResultset($view, $ordered_dataset, [
      '_entity' => 'title',
    ]);
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
   * @return array
   *   The sorted result set.
   *
   * @see \Drupal\Tests\views\Kernel\ViewsKernelTestBase::orderResultSet
   */
  protected function orderResultSet(array $result_set, $column, $reverse = FALSE) {
    $order = $reverse ? -1 : 1;
    usort($result_set, function ($a, $b) use ($column, $order) {
      if ($a[$column] == $b[$column]) {
        return 0;
      }
      return $order * (($a[$column] < $b[$column]) ? -1 : 1);
    });
    return $result_set;
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
   * Check anonymous user access to publications.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAnonymousUserAccess() {
    $this->visit('/publications');

    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $publication_settings_mut */
    $publication_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publication_settings_mut
      ->set('filter_publication_types', $this->defaultFilterPublicationTypeSettings)
      ->set('biblio_order', $this->defaultSortOrder)
      ->save();

    parent::tearDown();
  }

}
