<?php declare(strict_types = 1);

namespace Drupal\jsonapi_cross_bundle;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\jsonapi_cross_bundle\ResourceType\JsonapiExtrasCrossBundleResourceTypeRepository;

final class JsonapiCrossBundleServiceProvider extends ServiceProviderBase {
  public function alter(ContainerBuilder $container) {
    // We cannot use the module handler as the container is not yet compiled.
    // @see \Drupal\Core\DrupalKernel::compileContainer()
    $modules = $container->getParameter('container.modules');
    if (isset($modules['jsonapi_extras'])) {
      $definition = $container->getDefinition('jsonapi_cross_bundle.cross_bundle_resource_type_repository');
      $definition->setClass(JsonapiExtrasCrossBundleResourceTypeRepository::class);
    }
  }
}
