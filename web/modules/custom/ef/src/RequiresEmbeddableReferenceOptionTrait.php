<?php

namespace Drupal\ef;

/**
 * Class RequiresEmbeddableReferenceOptionTrait
 *
 * Allows subsclasses to have a quick access to cached embeddable reference
 * option plugins.
 *
 * @package Drupal\ef
 */
trait RequiresEmbeddableReferenceOptionTrait {
  use EmbeddableViewModeHelperTrait;

  /**
   * Returns an array of configured EmbeddableReferenceOptionsPluginInterface
   * objects
   *
   * @param string $embeddable_bundle
   * @param string $view_mode
   *
   * @return array EmbeddableReferenceOptionsPluginInterface
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getEnabledReferenceOptions (string $embeddable_bundle, string $view_mode) {
    $options = [];

    /** @var $embeddableReferenceOptionsPluginManager \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager */
    $embeddableReferenceOptionsPluginManager = \Drupal::service('plugin.manager.embeddable_reference_options');

    $embeddableReferenceOptionPluginDefinitions = $embeddableReferenceOptionsPluginManager->getDefinitions();

    $settings = $this->getThirdPartySettingForEmbeddableBundleAndViewMode($embeddable_bundle, $view_mode,'embeddable_reference_options');

    if (count($embeddableReferenceOptionPluginDefinitions) > 0) {
      foreach ($embeddableReferenceOptionPluginDefinitions as $embeddableReferenceOptionPluginDefinition) {
        $plugin_id = $embeddableReferenceOptionPluginDefinition['id'];

        if (isset($settings[$plugin_id]['enabled']) && $settings[$plugin_id]['enabled']) {
          $plugin_config = isset($settings[$plugin_id]['configuration']) ? $settings[$plugin_id]['configuration'] : [];

          /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $configured_plugin */
          $configured_plugin = $embeddableReferenceOptionsPluginManager->createInstance($plugin_id, $plugin_config);

          $options[] = $configured_plugin;

        }
      }
    }

    return $options;
  }
}