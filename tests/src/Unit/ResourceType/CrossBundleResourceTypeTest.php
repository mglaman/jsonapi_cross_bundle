<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel\ResourceType;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\jsonapi_cross_bundle\ResourceType\CrossBundlesResourceType;
use Drupal\Tests\UnitTestCase;

/**
 * @group jsonapi_cross_bundle
 * @coversDefaultClass \Drupal\jsonapi_cross_bundle\ResourceType\CrossBundlesResourceType
 */
final class CrossBundleResourceTypeTest extends UnitTestCase {

  public function testGetBundle() {
    $resource_type = new CrossBundlesResourceType(
      'entity_test',
      'entity_test',
      EntityTest::class
    );
    $this->assertNull($resource_type->getBundle());
  }

  public function testGetPath() {
    $resource_type = new CrossBundlesResourceType(
      'entity_test',
      'entity_test',
      EntityTest::class
    );
    $this->assertEquals('entity_test', $resource_type->getPath());
  }

}
