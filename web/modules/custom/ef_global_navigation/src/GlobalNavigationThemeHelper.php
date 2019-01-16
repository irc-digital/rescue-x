<?php

namespace Drupal\ef_global_navigation;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalNavigationThemeHelper implements ContainerInjectionInterface {

  public function __construct() {
  }

  public static function create(ContainerInterface $container) {
    return new static(
    );
  }

  /**
   * Ready the global navigation as a pattern
   *
   * @param $variables
   */
  public function preprocessGlobalNavigation (&$variables) {
    $variables['global_navigation'] = [
      '#type' => "pattern",
      '#id' => 'global_navigation',
      '#fields' => [
        'global_navigation_crisis_watch' => [
          '#theme' => 'crisis_watch',
          '#location' => 'header',
        ],
        'global_navigation_secondary_menu' => [
          '#theme' => 'ef_secondary_menu',
        ],
      ],
    ];
  }
}