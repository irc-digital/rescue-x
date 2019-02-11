<?php


namespace Drupal\ef;

use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * LocalActionToToolbarLinkBuilder fills out the placeholders generated in ef_toolbar().
 */
class LocalActionToToolbarLinkBuilder {

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\ef\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * LocalActionToToolbarLinkBuilder constructor.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $localTaskManager
   * @param \Drupal\ef\RouteMatchInterface $routeMatch
   */
  public function __construct(AccountProxyInterface $account, LocalTaskManagerInterface $localTaskManager, RouteMatchInterface $routeMatch) {
    $this->account = $account;
    $this->localTaskManager = $localTaskManager;
    $this->routeMatch = $routeMatch;
  }

  public function renderToolbarLinks() {
    $links = [];

    $local_tasks = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName());

    if (empty($local_tasks['tabs'])) {
      return $links;
    }

    foreach ($local_tasks['tabs'] as $route_name => $value) {
      /** @var \Drupal\Core\Url $url */
      $url = $value['#link']['url'];

      if ($url->access($this->account)) {
        // Add to array by #weight so that we have the correct order
        $links[$value['#weight']] = $value['#link'];
      }
    }

    // Sort into correct order
    ksort($links);

    $build = [
      '#theme' => 'links__toolbar_ef',
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
      '#links' => $links,
      '#cache' => [
        'contexts' => ['route', 'user.permissions'],
      ],
    ];

    return $build;
  }
}