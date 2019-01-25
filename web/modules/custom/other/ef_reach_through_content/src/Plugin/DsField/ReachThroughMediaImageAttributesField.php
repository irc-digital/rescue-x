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
}
