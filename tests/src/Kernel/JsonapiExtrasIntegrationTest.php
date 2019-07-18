<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel;

use Drupal\jsonapi_cross_bundle\ResourceType\JsonapiExtrasCrossBundleResourceTypeRepository;

/**
 * @group jsonapi_cross_bundle
 * @requires module jsonapi_extras
 */
final class JsonapiExtrasIntegrationTest extends JsonapiCrossBundleTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['jsonapi_extras'];

  public function testResourceTypeRepositoryDefinition() {
    $resource_type_repository = $this->container->get('jsonapi.resource_type.repository');
    $this->assertInstanceOf(JsonapiExtrasCrossBundleResourceTypeRepository::class, $resource_type_repository);
  }

}
