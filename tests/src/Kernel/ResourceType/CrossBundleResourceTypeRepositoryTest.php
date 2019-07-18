<?php

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel\ResourceType;

use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\Tests\jsonapi_cross_bundle\Kernel\JsonapiCrossBundleTestBase;

final class CrossBundleResourceTypeRepositoryTest extends JsonapiCrossBundleTestBase {

  public function testRootEntityTypeResourceType() {
    $resource_type_repository = $this->container->get('jsonapi.resource_type.repository');
    $resource_types = $resource_type_repository->all();

    $filtered_resource_types = array_filter($resource_types, static function (ResourceType $resource_type) {
      return $resource_type->getEntityTypeId() === 'entity_test' && $resource_type->getBundle() === NULL;
    });
    $this->assertCount(1, $filtered_resource_types);
  }

}
