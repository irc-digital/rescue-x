<?php

namespace Drupal\ef_reach_through_content\Plugin\DsField;

use Drupal\ef_patterns\Plugin\DsField\MediaImageAttributesField;

/**
 * Defines a DS field that knows how to reach through the entity reference to
 * get image information
 *
 * @DsField(
 *   id = "reach_through_media_image_attributes_field",
 *   deriver = "Drupal\ef_reach_through_content\Plugin\Derivative\ReachThroughMediaImageAttributesField"
 * )
 */
class ReachThroughMediaImageAttributesField extends MediaImageAttributesField {

  protected function getEntityWithImageField() {
    $reach_through_entity = $this->entity();

    $outer_entity = $reach_through_entity->reach_through_ref->entity;

    $reach_through_fields = \Drupal::service('ef.reach_through_service')->getReachThoughtFieldMappings($reach_through_entity);
    
//    $outer_entity = $this->entity();

    $config = $this->getConfiguration();
    $field_name = $config['field']['field_name'];
    $outer_field_name = $reach_through_fields[$field_name];
    $media_image_entity = $outer_entity->{$outer_field_name}->entity;

    return $media_image_entity;
  }
}
