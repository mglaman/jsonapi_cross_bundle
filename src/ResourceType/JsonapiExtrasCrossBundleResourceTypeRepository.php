<?php declare(strict_types = 1);

namespace Drupal\jsonapi_cross_bundle\ResourceType;

use Drupal\jsonapi_extras\ResourceType\ConfigurableResourceTypeRepository;

final class JsonapiExtrasCrossBundleResourceTypeRepository extends ConfigurableResourceTypeRepository {
  use CrossBundleResourceTypeRepositoryTrait;
}
