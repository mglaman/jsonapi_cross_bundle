<?php declare(strict_types = 1);

namespace Drupal\Tests\jsonapi_cross_bundle\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

abstract class JsonapiCrossBundleTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['serialization', 'jsonapi', 'jsonapi_cross_bundle'];

}
