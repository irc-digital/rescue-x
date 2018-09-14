<?php

namespace Drupal\ef_patterns;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 *
 */
class EfPatternsServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->setParameter('ui_patterns_library.file_extensions', ['.rp.yml']);
  }

}
