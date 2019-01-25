<?php

namespace Drupal\ef_reach_through_content\Plugin\Derivative;

use Drupal\ef_patterns\Plugin\Derivative\MediaImageAttributesField;
use Drupal\field\FieldConfigInterface;

/**
 * Provides a deriver to generate media attribute DS fields that know how to
 * reach through to the underlying entity
 */
class ReachThroughMediaImageAttributesField extends MediaImageAttributesField {
  protected function supportsEntityType ($entity_type_id) {
    return $entity_type_id == 'reach_through';
  }

  protected function generateTitle (FieldConfigInterface $field, $attribute_label) {
    return t('Reach-through @label (@attribute)', ['@label' => $field->label(), '@attribute' => $attribute_label]);
  }
}