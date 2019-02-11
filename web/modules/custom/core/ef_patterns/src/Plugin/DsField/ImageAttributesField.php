<?php

namespace Drupal\ef_patterns\Plugin\DsField;

/**
 * Defines a image field.
 *
 * @DsField(
 *   id = "image_attributes_field",
 *   deriver = "Drupal\ef_patterns\Plugin\Derivative\ImageAttributesField"
 * )
 */
class ImageAttributesField extends BaseImageAttributesField {
  protected function getEntityWithImageField() {
    return $this->entity();
  }

  protected function getImageFieldName() {
    $config = $this->getConfiguration();
    return $config['field']['field_name'];
  }

}
