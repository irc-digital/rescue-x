<?php

namespace Drupal\ef_patterns\Plugin\Derivative;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Provides a derivative for link fields so we can pull out the link text and
 * the URL separately to pass them through to a UI Pattern
 */
class LinkAttributesField extends UnpackedAttributeField {

  protected function fieldMatchesCriteria(FieldDefinitionInterface $fieldDefinition) {
    return $fieldDefinition->getType() == 'link';
  }

  protected function getFieldAttributes() {
    return [
      'url' => 'Link URL',
      'link_text' => 'Link text',
    ];
  }

}