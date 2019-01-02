<?php

namespace Drupal\ef\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\ef\EmbeddableInterface;

interface EmbeddableReferenceOptionsPluginInterface extends PluginFormInterface, ConfigurablePluginInterface {
  /**
   * Returns the id of the plugin
   *
   * @return string
   */
  function getId();

  /**
   * Returns the label of the plugin
   *
   * @return string
   */
  public function getLabel();

  /**
   * Reference option plugins provide a small amount of form content to output
   * the form fields needed for the editor.
   *
   * Currently, we do not support anything super fancy in this regard. Simple
   * form element, whose values are handled by the calling class
   *
   * @param string $embeddable_bundle
   * @param array $values
   * @return array
   */
  function buildForm ($embeddable_bundle, array $values);

  /**
   * Returns the options that the editor selected
   *
   * @param $options
   * @return mixed|NULL if the option does not need to be used as part of building
   */
  function getOptionValue ($options);

}