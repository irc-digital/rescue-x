<?php

namespace Drupal\ef_patterns\Plugin\Derivative;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Provides a derivative for image fields so we can pull out the elements and
 * pass them into a pattern.
 */
class ImageAttributesField extends BaseImageAttributesField {

  protected function fieldMatchesCriteria(FieldDefinitionInterface $fieldDefinition) {
    return $fieldDefinition->getType() == 'image';
  }

}