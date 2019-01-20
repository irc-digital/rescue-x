<?php


namespace Drupal\ef;


use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocalActionToToolbarHelper implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\ef\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  public function __construct(LocalTaskManagerInterface $localTaskManager, ThemeManagerInterface $themeManager, RouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
    $this->localTaskManager = $localTaskManager;
    $this->themeManager = $themeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.local_task'),
      $container->get('theme.manager'),
      $container->get('current_route_match')
    );
  }

  public function moveLocalActionsToToolbar () {
    $links = [];

    /** @var \Drupal\Core\Theme\ActiveTheme $active_theme */
    $active_theme = $this->themeManager->getActiveTheme();

    $items = [];

    if ($active_theme->getName() != 'seven') {
      $local_tasks = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName());

      if (empty($local_tasks['tabs'])) {
        return $links;
      }

      foreach ($local_tasks['tabs'] as $route_name => $value) {
        // Add to array by #weight so that we have the correct order
        $links[$value['#weight']] = $value['#link'];
      }

      // Sort into correct order
      ksort($links);

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
          '#tag' => 'div',
          '#value' => t('Page actions'),
          '#attributes' => [
            'title' => t('Local tasks'),
            'class' => ['toolbar-icon', 'toolbar-icon-page-action'],
          ],
        ],
        'tray' => [
          '#heading' => t('Local tasks'),
          'toolbar_administration' => [
            '#attributes' => [
              'class' => ['toolbar-menu'],
            ],
            '#links' => $links,
            '#theme' => 'links__toolbar_ef',
          ],
        ],
        '#weight' => 1000,
      ];
    }

    return $items;
  }
}