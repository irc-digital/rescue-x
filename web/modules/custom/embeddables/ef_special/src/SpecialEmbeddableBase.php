<?php

namespace Drupal\ef_special;

use Drupal\Component\Plugin\PluginBase;

/**
 * A base class to help developers implement their own sandwich plugins.
 *
 *
 * @see \Drupal\ef_special\Annotation\SpecialEmbeddable
 * @see \Drupal\ef_special\SpecialEmbeddableInterface
 */
abstract class SpecialEmbeddableBase extends PluginBase implements SpecialEmbeddableInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    // Retrieve the @description property from the annotation and return it.
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $values) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function render (array $values) {
    return array();
  }
}
