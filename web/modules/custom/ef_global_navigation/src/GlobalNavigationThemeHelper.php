<?php

namespace Drupal\ef_global_navigation;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalNavigationThemeHelper implements ContainerInjectionInterface {

  /** @var \Drupal\ef_global_navigation\SitewideDonationLinkServiceInterface */
  protected $sitewideDonationLinkService;

  public function __construct(SitewideDonationLinkServiceInterface $sitewideDonationLinkService) {
    $this->sitewideDonationLinkService = $sitewideDonationLinkService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_donation_link_service')
    );
  }

  /**
   * Ready the global navigation as a pattern
   *
   * @param $variables
   */
  public function preprocessGlobalNavigation (&$variables) {
    $global_navigation_cta = $this->sitewideDonationLinkService->getSitewideDonationLinkInformation();

    $variables['global_navigation'] = [
      '#type' => "pattern",
      '#id' => 'global_navigation',
      '#fields' => [
        'global_navigation_cta_url' => $global_navigation_cta['url'],
        'global_navigation_cta_text' => $global_navigation_cta['title'],
        'global_navigation_cta_icon_name' => $global_navigation_cta['icon'],
        'global_navigation_crisis_watch' => [
          '#theme' => 'ef_crisis_watch',
          '#location' => 'header',
        ],
        'global_navigation_main_menu' => [
          '#theme' => 'ef_main_menu',
        ],
        'global_navigation_secondary_menu' => [
          '#theme' => 'ef_secondary_menu',
        ],
        'global_navigation_mobile_secondary_menu' => [
          '#theme' => 'ef_secondary_menu_mobile',
        ],
        'global_navigation_branding' => [
          '#type' => 'pattern',
          '#id' => 'global_navigation_branding_link',
          '#fields' => [
            'global_navigation_branding_link_modifiers' => ['context-usa'],
          ],
        ],
      ],
    ];
  }
}