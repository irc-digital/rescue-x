<?php

namespace Drupal\ef\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by whether the embeddable can be used in a WYSIWYG.
 *
 * This is determined by whether the Entity Embed view mode has been created for
 * the embeddable type
 *
 * @ingroup views_filter_handlers
 * @ViewsFilter("ef_embeddable_in_wysiwyg")
 */
class EmbeddableInWYSIYWG extends InOperator implements ContainerFactoryPluginInterface {
  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface  */
  protected $entityTypeBundleInfo;

  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  public function query() {
    if (empty($this->value)) {
      $this->value = array_keys($this->getAllPermittedEmbeddableBundles());
    }

    if (count($this->value) == 1 && $this->value[0] == 'all_able') {
      $all = $this->getAllPermittedEmbeddableBundles();
      unset($all['all_able']);
      $this->value =  array_keys($all);
    }

    $this->opSimple();
  }

  /**
   * Skip validation if no options have been chosen so we can use it as a
   * non-filter.
   */
  public function validate() {
    if (!empty($this->value)) {
      parent::validate();
    }
  }

  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = $this->getAllPermittedEmbeddableBundles();
      $this->options['value'] = $this->valueOptions;
    }

    return $this->valueOptions;
  }

  protected function getAllPermittedEmbeddableBundles () {
    $entity_view_storage = $this->entityTypeManager->getStorage('entity_view_display');

    $view_displays_with_entity_embed_setup_ids = $entity_view_storage->getQuery()
      ->condition('targetEntityType','embeddable', '=')
      ->condition('mode', 'entity_embed', '=')
      ->execute();

    $view_displays_with_entity_embed_setup = $entity_view_storage->loadMultiple($view_displays_with_entity_embed_setup_ids);

    $embeddble_bundle_info = $this->entityTypeBundleInfo->getBundleInfo('embeddable');

    $bundle_list = [
      'all_able' => '- Any -',
    ];

    foreach ($view_displays_with_entity_embed_setup as $view_display) {
      $bundle_list[$view_display->getTargetBundle()] = $embeddble_bundle_info[$view_display->getTargetBundle()]['label'];
    }

    return $bundle_list;
  }
}
