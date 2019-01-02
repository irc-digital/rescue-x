<?php

namespace Drupal\ef_patterns\Plugin\DsField;

/**
 * Defines a image field (via an entity reference to a media image entity).
 *
 * @DsField(
 *   id = "media_image_attributes_field",
 *   deriver = "Drupal\ef_patterns\Plugin\Derivative\MediaImageAttributesField"
 * )
 */
class MediaImageAttributesField extends BaseImageAttributesField {
  protected function getEntityWithImageField() {
    $config = $this->getConfiguration();
    $field_name = $config['field']['field_name'];

    $outer_entity = $this->entity();
    $media_image_entity = $outer_entity->{$field_name}->entity;

    return $media_image_entity;
  }

  protected function getImageFieldName() {
    return 'field_ef_image';
  }

}
