<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel;

/**
 * @group jsonapi_cross_bundle
 * @requires module jsonapi_extras
 *
 * @todo this will fail until https://www.drupal.org/project/jsonapi_extras/issues/3068811 lands
 */
final class JsonapiExtrasIntegrationTest extends JsonapiCrossBundleTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['jsonapi_extras'];


  public function testResourceTypeRepositoryDefinition() {
    $resource_type_repository = $this->container->get('jsonapi.resource_type.repository');
    $this->assertTrue(TRUE, 'JSON:API Extras did not cause a conflicting crash.');
  }

}
