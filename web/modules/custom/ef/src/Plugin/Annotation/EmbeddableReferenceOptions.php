<?php

namespace Drupal\ef\Plugin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a EmbeddableReferenceOptions annotation object.
 *
 * This is used to allow plugins to implement embedding options - specifically
 * used to provide the modifiers functionality.
 *
 * @Annotation
 */
class EmbeddableReferenceOptions extends Plugin  {
  /**
   * @var string $label
   */
  public $label;
}
