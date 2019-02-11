<?php


namespace Drupal\ef;


use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocalActionToToolbarHelper implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  public function __construct(ThemeManagerInterface $themeManager) {

    $this->themeManager = $themeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme.manager')
    );
  }

  public function moveLocalActionsToToolbar () {

    /** @var \Drupal\Core\Theme\ActiveTheme $active_theme */
    $active_theme = $this->themeManager->getActiveTheme();

    $items = [];

    if ($active_theme->getName() != 'seven') {
      // Add the menu local tasks into the toolbar.
      $items['local_tasks'] = [
        '#type' => 'toolbar_item',
        '#attached' => [
          'library' => [
            'ef/page-action-icon',
          ],
        ],
        'tab' => [
          '#type' => 'html_tag',
          '#cache' => [
            'contexts' => ['user.roles:anonymous'],
          ],
          '#tag' => 'div',
          '#value' => t('Page actions'),
          '#attributes' => [
            'title' => t('Local tasks'),
            'class' => ['toolbar-icon', 'toolbar-icon-page-action'],
          ],
        ],
        'tray' => [
          '#heading' => t('Local tasks'),
        ],
        '#weight' => 1000,
      ];
    }

    $items['local_tasks']['tray']['toolbar_administration'] = [
      '#lazy_builder' => ['ef.toolbar_link_builder:renderToolbarLinks', []],
      '#create_placeholder' => TRUE,
    ];

    return $items;
  }
}