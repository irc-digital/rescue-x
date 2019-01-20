<?php

namespace Drupal\ef_patterns\Plugin\Derivative;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Provides a derivative for image types on media entity reference fields so we can pull out the elements and
 * pass them into a pattern.
 */
class MediaImageAttributesField extends BaseImageAttributesField {

  protected function fieldMatchesCriteria(FieldDefinitionInterface $field_definition) {
    if ($field_definition->getType() == 'entity_reference') {
      $field_storage_definition = $field_definition->getFieldStorageDefinition();

      if ($field_storage_definition->getSetting('target_type') == 'media') {
        $handler_settings = $field_definition->getSetting('handler_settings');
        return isset($handler_settings['target_bundles']) && count($handler_settings['target_bundles']) == 1 && isset($handler_settings['target_bundles']['ef_image']);
      }
    }
    return FALSE;
  }

}