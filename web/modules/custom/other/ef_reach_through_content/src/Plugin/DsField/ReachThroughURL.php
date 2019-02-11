<?php

namespace Drupal\ef_reach_through_content\Plugin\DsField;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A DS field that produces a URL for a reach-through entity
 *
 * @DsField(
 *   id = "reach_through_url",
 *   entity_type = "reach_through",
 *   title = "Reach-through URL"
 * )
 *
 */
class ReachThroughURL extends DsFieldBase {

  /** @var \Drupal\Core\Path\AliasManagerInterface */
  protected $aliasManager;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, AliasManagerInterface $aliasManager) {
    $this->aliasManager = $aliasManager;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\ef_reach_through_content\Entity\ReachThrough $reach_through_entity */
    $reach_through_entity = $this->entity();

    /** @var \Drupal\node\NodeInterface $wrapped_node */
    $wrapped_node = $reach_through_entity->reach_through_ref->entity;

    $url = $this->aliasManager->getAliasByPath($wrapped_node->toUrl()->toString());

    return [
      '#markup' => $url,
    ];
  }
}
