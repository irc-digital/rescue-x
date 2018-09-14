<?php

namespace Drupal\ef_wysiwyg_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityWysiwyg;
use Drupal\entity_embed\Annotation\EntityEmbedDisplay;
use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\EntityReferenceFieldFormatter;

/**
 * Class EmbeddableFieldFormatter
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference_embeddable_entity_display",
 *   label = @Translation("Embeddable"),
 *   deriver = "Drupal\ef_wysiwyg_embed\Plugin\Derivative\EmbeddableFieldFormatterDeriver",
 *   field_type = "entity_reference_embeddable"
 * )
 *
 * @package Drupal\ef\Plugin\entity_embed\EntityEmbedDisplay
 */
class EmbeddableFieldFormatter extends EntityReferenceFieldFormatter {
  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    if (!isset($this->fieldDefinition)) {
      $this->fieldDefinition = parent::getFieldDefinition();

      $this->fieldDefinition->setSetting('target_type', $this->getEntityTypeFromContext());

      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $this->getEntityFromContext();

      if ($entity) {
        $bundle = $entity->bundle();
        $this->fieldDefinition->setSetting('handler_settings', ['target_bundles' => [$bundle => $bundle]]);
      }

      $this->fieldDefinition->setSetting('ef_view_mode_visibility_usage_context', EmbeddableViewModeVisibilityWysiwyg::class);
    }
    return $this->fieldDefinition;
  }
}