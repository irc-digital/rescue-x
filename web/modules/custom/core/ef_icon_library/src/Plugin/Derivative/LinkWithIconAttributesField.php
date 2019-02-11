<?php

namespace Drupal\ef_icon_library\Plugin\Derivative;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\ef_patterns\Plugin\Derivative\LinkAttributesField;

/**
 * Provides a derivative for the link with icon field
 */
class LinkWithIconAttributesField extends LinkAttributesField {

  protected function fieldMatchesCriteria(FieldDefinitionInterface $fieldDefinition) {
    return $fieldDefinition->getType() == 'link_icon';
  }

  protected function getFieldAttributes() {
    return [
      'link_icon' => 'Link icon',
      ] + parent::getFieldAttributes();
  }

}