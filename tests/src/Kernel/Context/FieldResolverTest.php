<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel\Context;

use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\jsonapi_cross_bundle\Kernel\JsonapiCrossBundleTestBase;

final class FieldResolverTest extends JsonapiCrossBundleTestBase {

  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test_with_bundle');
    $this->makeBundle('bundle1');
    $this->makeBundle('bundle2');
    $this->makeBundle('bundle3');

    $this->makeField('string', 'field_test1', 'entity_test_with_bundle', ['bundle1']);
    $this->makeField('string', 'field_test2', 'entity_test_with_bundle', ['bundle1']);
    $this->makeField('string', 'field_test3', 'entity_test_with_bundle', ['bundle2', 'bundle3']);
  }

  public function testResolveInternalEntityQueryPath() {
    $resource_type_repository = $this->container->get('jsonapi.resource_type.repository');
    $field_resolver = $this->container->get('jsonapi.field_resolver');
    $resource_type = $resource_type_repository->getByTypeName('entity_test_with_bundle--entity_test_with_bundle');
    $this->assertNotNull($resource_type);

    // @todo this exception doesn't throw when filtering on the collection
    $internal_include_path = $field_resolver->resolveInternalEntityQueryPath($resource_type->getEntityTypeId(), $resource_type->getBundle(), 'field_test1');
    $this->assertEquals('', $internal_include_path);

  }

  /**
   * Create a simple bundle.
   *
   * @param string $name
   *   The name of the bundle to create.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function makeBundle($name) {
    EntityTestBundle::create([
      'id' => $name,
    ])->save();
  }

  /**
   * Creates a field for a specified entity type/bundle.
   *
   * @param string $type
   *   The field type.
   * @param string $name
   *   The name of the field to create.
   * @param string $entity_type
   *   The entity type to which the field will be attached.
   * @param string[] $bundles
   *   The entity bundles to which the field will be attached.
   * @param array $storage_settings
   *   Custom storage settings for the field.
   * @param array $config_settings
   *   Custom configuration settings for the field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function makeField($type, $name, $entity_type, array $bundles, array $storage_settings = [], array $config_settings = []) {
    $storage_config = [
      'field_name' => $name,
      'type' => $type,
      'entity_type' => $entity_type,
      'settings' => $storage_settings,
    ];

    FieldStorageConfig::create($storage_config)->save();

    foreach ($bundles as $bundle) {
      FieldConfig::create([
        'field_name' => $name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'settings' => $config_settings,
      ])->save();
    }
  }

}
