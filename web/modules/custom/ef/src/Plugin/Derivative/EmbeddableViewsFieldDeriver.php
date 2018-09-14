<?php

namespace Drupal\ef\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 *
 */
class EmbeddableViewsFieldDeriver extends DeriverBase implements ContainerDeriverInterface {

  protected $basePluginId;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface */
  protected $moduleHandler;

  /**
   * EmbeddableViewsFieldDeriver constructor.
   * @param ModuleHandlerInterface $moduleHandler
   */
  public function __construct($basePluginId, ModuleHandlerInterface $moduleHandler) {
    $this->basePluginId = $basePluginId;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    // allow embeddables to declare views that they need to have available on their
    // display.
    $views_field_infos = \Drupal::moduleHandler()->invokeAll('embeddable_views_field_info');

    foreach ($views_field_infos as $views_field_info) {
      $key = $views_field_info['id'];

      $this->derivatives[$key] = [
          'title' => sprintf ($views_field_info['label']),
          'entity_type' => 'embeddable',
          'ui_limit' => [sprintf("%s|*", $views_field_info['embeddable'])],
          'field_name' => $views_field_info['id'],
          'id' => sprintf('%s:%s', $this->basePluginId, $key),
      ] + $views_field_info + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
