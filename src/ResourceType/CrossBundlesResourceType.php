<?php declare(strict_types = 1);

namespace Drupal\jsonapi_cross_bundle\ResourceType;

use Drupal\jsonapi\ResourceType\ResourceType;

/**
 * Resource type that allows collections for all bundles in an entity type.
 */
final class CrossBundlesResourceType extends ResourceType {
  /**
   * {@inheritdoc}
   */
  public function getPath(): string {
    return $this->entityTypeId;
  }
  /**
   * {@inheritdoc}
   */
  public function getBundle(): ?string {
    return NULL;
  }
}
