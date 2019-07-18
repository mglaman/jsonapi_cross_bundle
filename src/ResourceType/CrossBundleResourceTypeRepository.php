<?php declare(strict_types = 1);

namespace Drupal\jsonapi_cross_bundle\ResourceType;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\ContentEntityNullStorage;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepository;

final class CrossBundleResourceTypeRepository extends ResourceTypeRepository {
  use CrossBundleResourceTypeRepositoryTrait;
}
