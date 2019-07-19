<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Unit\Routing;

use Drupal\Core\Entity\EntityInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\Routing\Routes;
use Drupal\jsonapi_cross_bundle\ResourceType\CrossBundleResourceTypeRepository;
use Drupal\jsonapi_cross_bundle\ResourceType\CrossBundlesResourceType;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CrossBundleRoutesTest extends UnitTestCase {

  public function testCrossBundleCollectionRoute() {
    $type_1 = new CrossBundlesResourceType('entity_test', 'entity_test', EntityInterface::class);
    $type_2 = new ResourceType('entity_test', 'bundle_1', EntityInterface::class);
    $type_3 = new ResourceType('entity_test', 'bundle_2', EntityInterface::class);

    $type_1->setRelatableResourceTypes([]);
    $type_2->setRelatableResourceTypes([]);
    $type_3->setRelatableResourceTypes([]);

    $resource_type_repository = $this->prophesize(CrossBundleResourceTypeRepository::class);
    $resource_type_repository->all()->willReturn([$type_1, $type_2, $type_3]);
    $container = $this->prophesize(ContainerInterface::class);
    $container->get('jsonapi.resource_type.repository')->willReturn($resource_type_repository->reveal());
    $container->getParameter('jsonapi.base_path')->willReturn('/jsonapi');
    $container->getParameter('authentication_providers')->willReturn([
      'lorem' => [],
      'ipsum' => [],
    ]);

    $routes = Routes::create($container->reveal())->routes();
    $this->assertCount(13, $routes);

    $iterator = $routes->getIterator();
    // Check the cross bundle collection route.
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $iterator->offsetGet('jsonapi.entity_test--entity_test.collection');
    $this->assertSame('/jsonapi/entity_test', $route->getPath());
    // @todo we need to disable this route, somehow.
    $route = $iterator->offsetGet('jsonapi.entity_test--entity_test.individual');
    $this->assertSame('/jsonapi/entity_test/{entity}', $route->getPath());
  }

}
