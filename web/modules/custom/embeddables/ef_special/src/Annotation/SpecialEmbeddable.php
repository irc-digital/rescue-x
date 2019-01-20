<?php

namespace Drupal\ef_special\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Special Embeddable annotation object.
 *
 * @see \Drupal\ef_special\SpecialEmbeddablePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class SpecialEmbeddable extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin name
   *
   * @var string
   */
  public $name;

  /**
   * A brief, human readable, description of the special embeddable type.
   *
   * This property is designated as being translatable because it will appear
   * in the user interface. This provides a hint to other developers that they
   * should use the Translation() construct in their annotation when declaring
   * this property.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * A prettier id. This will be used for naming the component in CSS
   *
   */
  public $nice_id;
}
