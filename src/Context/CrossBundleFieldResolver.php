<?php declare(strict_types = 1);

namespace Drupal\jsonapi_cross_bundle\Context;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\jsonapi\Context\FieldResolver;
use Drupal\jsonapi\ResourceType\ResourceType;

final class CrossBundleFieldResolver extends FieldResolver {

  /**
   * Get all item definitions from a set of resources types by a field name.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The resource types on which the field might exist.
   * @param string $field_name
   *   The field for which to retrieve field item definitions.
   *
   * @return \Drupal\Core\TypedData\ComplexDataDefinitionInterface[]
   *   The found field item definitions.
   */
  protected function getFieldItemDefinitions(array $resource_types, $field_name): array {
    return array_reduce($resource_types, function ($result, ResourceType $resource_type) use ($field_name) {
      /* @var \Drupal\jsonapi\ResourceType\ResourceType $resource_type */
      $entity_type = $resource_type->getEntityTypeId();
      $bundle = $resource_type->getBundle();
      if ($bundle !== NULL) {
        $definitions = $this->fieldManager->getFieldDefinitions($entity_type, $bundle);
        if (isset($definitions[$field_name])) {
          $result[$resource_type->getTypeName()] = $definitions[$field_name]->getItemDefinition();
        }
      } else {
        $bundles = array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type));
        foreach ($bundles as $bundle) {
          $definitions = $this->fieldManager->getFieldDefinitions($entity_type, $bundle);
          if (isset($definitions[$field_name])) {
            $result[$resource_type->getTypeName()] = $definitions[$field_name]->getItemDefinition();
          }
        }
      }
      return $result;
    }, []);
  }

  /**
   * Gets the field access result for the 'view' operation.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The JSON:API resource type on which the field exists.
   * @param string $internal_field_name
   *   The field name for which access should be checked.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The 'view' access result.
   */
  protected function getFieldAccess(ResourceType $resource_type, $internal_field_name): AccessResultInterface {
    if ($resource_type->getBundle() === NULL) {
      // @todo access control concerns?
      return AccessResult::allowed();
    }
    $definitions = $this->fieldManager->getFieldDefinitions($resource_type->getEntityTypeId(), $resource_type->getBundle());
    assert(isset($definitions[$internal_field_name]), 'The field name should have already been validated.');
    $field_definition = $definitions[$internal_field_name];
    $filter_access_results = $this->moduleHandler->invokeAll('jsonapi_entity_field_filter_access', [$field_definition, \Drupal::currentUser()]);
    $filter_access_result = array_reduce($filter_access_results, function (AccessResultInterface $combined_result, AccessResultInterface $result) {
      return $combined_result->orIf($result);
    }, AccessResult::neutral());
    if (!$filter_access_result->isNeutral()) {
      return $filter_access_result;
    }
    $entity_access_control_handler = $this->entityTypeManager->getAccessControlHandler($resource_type->getEntityTypeId());
    $field_access = $entity_access_control_handler->fieldAccess('view', $field_definition, NULL, NULL, TRUE);
    return $filter_access_result->orIf($field_access);
  }

}
