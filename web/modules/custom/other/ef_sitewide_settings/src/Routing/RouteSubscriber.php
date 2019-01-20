<?php

namespace Drupal\ef_sitewide_settings\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Makes sures the sitewide display settings are not made available to an admin
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $collection->get('entity.entity_view_display.sitewide_settings.default');
    $route->setRequirement('access', 'FALSE');
    //$route->setCondition('FALSE');
    $route = $collection->get('entity.entity_view_display.sitewide_settings.default');

    $collection->remove('entity.entity_view_display.sitewide_settings.view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Come after field_ui.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -101];
    return $events;
  }

}
