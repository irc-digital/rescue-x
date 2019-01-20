<?php

namespace Drupal\ef_social_menu;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ef_crisis_watch\CrisisWatchServiceInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_social_menu\SocialMenuServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SocialThemeHelper implements ContainerInjectionInterface {
  /**
   * The social menu service
   *
   * @var SocialMenuServiceInterface
   */
  protected $socialMenuService;

  public function __construct(SocialMenuServiceInterface $socialMenuService) {
    $this->socialMenuService = $socialMenuService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_social_menu_service')
    );
  }

  /**
   * Generate the social follow pattern
   *
   * @param $variables
   */
  public function preprocessSocialFollow (&$variables) {

    $variables['social_follow'] = [
      '#type' => "pattern",
      '#id' => 'social_share',
      '#fields' => [
        'social_share_sites' => $this->socialMenuService->getSocialSites(),
        'social_share_title' => $this->socialMenuService->getFollowText(),
      ],
    ];
  }
}