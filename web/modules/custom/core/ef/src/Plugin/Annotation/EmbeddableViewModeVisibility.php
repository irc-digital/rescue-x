<?php

namespace Drupal\ef\Plugin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a EmbeddableViewModeVisibility annotation object.
 *
 * @Annotation
 */
class EmbeddableViewModeVisibility extends Plugin  {
  /**
   * The human-readable name of the view mode visibility type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;
}
