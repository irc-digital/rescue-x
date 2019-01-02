<?php

namespace Drupal\ef\Plugin\EmbeddableViewModeVisibility;

use Drupal\Core\Annotation\Translation;
use Drupal\ef\Plugin\Annotation\EmbeddableViewModeVisibility;
use Drupal\ef\Plugin\EmbeddableViewModeVisibilityBase;

/**
 * Plugin implementation to support field visibility selection
 *
 * @EmbeddableViewModeVisibility(
 *   id = "field",
 *   label = @Translation("Field"),
 * )
 */
class EmbeddableViewModeVisibilityField extends EmbeddableViewModeVisibilityBase {

}