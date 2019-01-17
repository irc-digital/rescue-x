<?php

namespace Drupal\ef_crisis_watch;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CrisisWatchThemeHelper implements ContainerInjectionInterface {
  /**
   * The crisis watch service
   *
   * @var CrisisWatchServiceInterface
   */
  protected $crisisWatchService;

  public function __construct(CrisisWatchServiceInterface $crisisWatchService) {
    $this->crisisWatchService = $crisisWatchService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_crisis_watch_service')
    );
  }

  public function preprocessCrisisWatch (&$variables) {

    $crisis_watch = $this->crisisWatchService->getCrisisWatch();

    if (!is_null($crisis_watch)) {
      $variables['crisis_watch'] = [
        '#type' => 'pattern',
        '#id' => 'crisis_watch',
        '#fields' => [
          'crisis_watch_text' => $crisis_watch['title'],
          'crisis_watch_url' => $crisis_watch['url'],
          'crisis_watch_modifiers' => [
            'type-three'
          ],
        ],
      ];
    }
  }
}