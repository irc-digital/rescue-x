<?php

namespace Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility;

use Drupal\Core\Annotation\Translation;
use Drupal\ef\Plugin\Annotation\EmbeddableViewModeVisibility;
use Drupal\ef\Plugin\EmbeddableViewModeVisibilityBase;

/**
 * Plugin implementation to support WYSIWYG visibility selection
 *
 * @EmbeddableViewModeVisibility(
 *   id = "wysiwyg",
 *   label = @Translation("WYSIWYG"),
 * )
 */
class EmbeddableViewModeVisibilityWysiwyg extends EmbeddableViewModeVisibilityBase {

}