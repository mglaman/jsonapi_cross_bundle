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

trait CrossBundleResourceTypeRepositoryTrait {
  public function all() {
    $resource_types = parent::all();

    $cached = $this->staticCache->get('jsonapi.cross_bundle_resource_types', FALSE);
    if ($cached === FALSE) {
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
        if ($entity_type === 'search_api_document') {
          continue;
        }
        $raw_fields = $this->getAllBaseFieldNames($entity_type);
        $resource_type = new CrossBundlesResourceType(
          $entity_type->id(),
          $entity_type->id(),
          $entity_type->getClass(),
          $entity_type->isInternal(),
          $entity_type->getStorageClass() !== ContentEntityNullStorage::class,
          !$entity_type instanceof ConfigEntityTypeInterface,
          FALSE,
          static::getBaseFieldMapping($raw_fields, $entity_type)
        );
        // @todo make this work nicely.
        $relatable_resource_types = $this->calculateBaseRelatableResourceTypes($resource_type, $resource_types);
        $resource_type->setRelatableResourceTypes($relatable_resource_types);
        $resource_types[] = $resource_type;
      }
      $this->staticCache->set('jsonapi.cross_bundle_resource_types', $resource_types, Cache::PERMANENT, ['jsonapi_resource_types']);
    }

    return $cached ? $cached->data : $resource_types;
  }

  /**
   * Calculates relatable JSON:API resource types for a given resource type.
   *
   * This method has no affect after being called once.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The resource type repository.
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   A list of JSON:API resource types.
   *
   * @return array
   *   The relatable JSON:API resource types, keyed by field name.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function calculateBaseRelatableResourceTypes(ResourceType $resource_type, array $resource_types) {
    // For now, only fieldable entity types may contain relationships.
    $entity_type = $this->entityTypeManager->getDefinition($resource_type->getEntityTypeId());
    assert($entity_type !== NULL);
    if ($entity_type->entityClassImplements(FieldableEntityInterface::class)) {
      $bundles = array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()));
      $relatable_public = [];
      foreach ($bundles as $bundle) {
        $field_definitions = $this->entityFieldManager->getFieldDefinitions(
          $resource_type->getEntityTypeId(),
          $bundle
        );

        $relatable_internal = array_map(function ($field_definition) use ($resource_types) {
          return $this->getRelatableResourceTypesFromFieldDefinition($field_definition, $resource_types);
        }, array_filter($field_definitions, function ($field_definition) {
          return $this->isReferenceFieldDefinition($field_definition);
        }));

        $relatable_public = [];
        foreach ($relatable_internal as $internal_field_name => $value) {
          $relatable_public[$resource_type->getPublicName($internal_field_name)] = $value;
        }
      }
      return $relatable_public;
    }
    return [];
  }

  /**
   * Gets all field names for a given entity type and bundle.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for which to get all field names.
   *
   * @return string[]
   *   All field names.
   */
  protected function getAllBaseFieldNames(EntityTypeInterface $entity_type): array {
    if ($entity_type instanceof ContentEntityTypeInterface) {
      $bundles = array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()));
      $field_definitions = [[]];
      foreach ($bundles as $bundle) {
        $field_definitions[] = $this->entityFieldManager->getFieldDefinitions($entity_type->id(), $bundle);
      }
      $field_definitions = array_merge(...$field_definitions);
      return array_keys($field_definitions);
    }

    if ($entity_type instanceof ConfigEntityTypeInterface) {
      // @todo Uncomment the first line, remove everything else once https://www.drupal.org/project/drupal/issues/2483407 lands.
      // return array_keys($entity_type->getPropertiesToExport());
      $export_properties = $entity_type->getPropertiesToExport();
      if ($export_properties !== NULL) {
        return array_keys($export_properties);
      }

      return ['id', 'type', 'uuid', '_core'];
    }

    throw new \LogicException('Only content and config entity types are supported.');
  }

  /**
   * Gets the field mapping for the given field names and entity type + bundle.
   *
   * @param string[] $field_names
   *   All field names on a bundle of the given entity type.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for which to get the field mapping.
   *
   * @return array
   *   An array with:
   *   - keys are (real/internal) field names
   *   - values are either FALSE (indicating the field is not exposed despite
   *     not being internal), TRUE (indicating the field should be exposed under
   *     its internal name) or a string (indicating the field should not be
   *     exposed using its internal name, but the name specified in the string)
   */
  protected static function getBaseFieldMapping(array $field_names, EntityTypeInterface $entity_type) {
    assert(Inspector::assertAllStrings($field_names));
    assert($entity_type instanceof ContentEntityTypeInterface || $entity_type instanceof ConfigEntityTypeInterface);

    $mapping = [];

    // JSON:API resource identifier objects are sufficient to identify
    // entities. By exposing all fields as attributes, we expose unwanted,
    // confusing or duplicate information:
    // - exposing an entity's ID (which is not a UUID) is bad, but it's
    //   necessary for certain Drupal-coupled clients, so we alias it by
    //   prefixing it with `drupal_internal__`.
    // - exposing an entity's UUID as an attribute is useless (it's already part
    //   of the mandatory "id" attribute in JSON:API), so we disable it in most
    //   cases.
    // - exposing its revision ID as an attribute will compete with any profile
    //   defined meta members used for resource object versioning.
    // @see http://jsonapi.org/format/#document-resource-identifier-objects
    $id_field_name = $entity_type->getKey('id');
    $uuid_field_name = $entity_type->getKey('uuid');
    if ($uuid_field_name !== 'id') {
      $mapping[$uuid_field_name] = FALSE;
    }
    $mapping[$id_field_name] = "drupal_internal__$id_field_name";
    if ($entity_type->isRevisionable() && ($revision_id_field_name = $entity_type->getKey('revision'))) {
      $mapping[$revision_id_field_name] = "drupal_internal__$revision_id_field_name";
    }
    if ($entity_type instanceof ConfigEntityTypeInterface) {
      // The '_core' key is reserved by Drupal core to handle complex edge cases
      // correctly. Data in the '_core' key is irrelevant to clients reading
      // configuration, and is not allowed to be set by clients writing
      // configuration: it is for Drupal core only, and managed by Drupal core.
      // @see https://www.drupal.org/node/2653358
      $mapping['_core'] = FALSE;
    }

    // For all other fields,  use their internal field name also as their public
    // field name.  Unless they're called "id" or "type": those names are
    // reserved by the JSON:API spec.
    // @see http://jsonapi.org/format/#document-resource-object-fields
    foreach (array_diff($field_names, array_keys($mapping)) as $field_name) {
      if ($field_name === 'id' || $field_name === 'type') {
        $alias = $entity_type->id() . '_' . $field_name;
        if (isset($field_name[$alias])) {
          throw new \LogicException('The generated alias conflicts with an existing field. Please report this in the JSON:API issue queue!');
        }
        $mapping[$field_name] = $alias;
        continue;
      }

      // The default, which applies to most fields: expose as-is.
      $mapping[$field_name] = TRUE;
    }

    return $mapping;
  }
}
