<?php

namespace Drupal\ef_social_share;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ef_social_share\SocialMenuServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SocialThemeHelper implements ContainerInjectionInterface {
  /**
   * The social menu service
   *
   * @var \Drupal\ef_social_share\SocialServiceInterface
   */
  protected $socialService;

  public function __construct(SocialServiceInterface $socialService) {
    $this->socialService = $socialService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_social_service')
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
        'social_share_sites' => $this->socialService->getSocialMenu(),
        'social_share_title' => $this->socialService->getFollowText(),
      ],
    ];
  }
}