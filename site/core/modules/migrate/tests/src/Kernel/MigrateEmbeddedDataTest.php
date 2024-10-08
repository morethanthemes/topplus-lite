<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the EmbeddedDataSource plugin.
 *
 * @group migrate
 */
class MigrateEmbeddedDataTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests the embedded_data source plugin.
   */
  public function testEmbeddedData(): void {
    $data_rows = [
      ['key' => '1', 'field1' => 'f1value1', 'field2' => 'f2value1'],
      ['key' => '2', 'field1' => 'f1value2', 'field2' => 'f2value2'],
    ];
    $ids = ['key' => ['type' => 'integer']];
    $definition = [
      'migration_tags' => ['Embedded data test'],
      'source' => [
        'plugin' => 'embedded_data',
        'data_rows' => $data_rows,
        'ids' => $ids,
      ],
      'process' => [],
      'destination' => ['plugin' => 'null'],
    ];

    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);
    $source = $migration->getSourcePlugin();

    // Validate the plugin returns the source data that was provided.
    $results = [];
    /** @var \Drupal\migrate\Row $row */
    foreach ($source as $row) {
      $this->assertFalse($row->isStub());

      $data_row = $row->getSource();
      // The "data" row returned by getSource() also includes all source
      // configuration - we remove it so we see only the data itself.
      unset($data_row['plugin']);
      unset($data_row['data_rows']);
      unset($data_row['ids']);
      $results[] = $data_row;
    }
    $this->assertSame($data_rows, $results);

    // Validate the public APIs.
    $this->assertSameSize($data_rows, $source);
    $this->assertSame($ids, $source->getIds());
    $expected_fields = [
      'key' => 'key',
      'field1' => 'field1',
      'field2' => 'field2',
    ];
    $this->assertSame($expected_fields, $source->fields());
  }

}
