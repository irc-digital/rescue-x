<?php


namespace Drupal\ef\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;

/**
 * Defines the 'embeddable_parent_id' entity field type.
 *
 * This custom field type is used merely to prevent the embeddable_parent_entity_reference
 * formatter from being an option for all strings.
 *
 * @FieldType(
 *   id = "embeddable_parent_id",
 *   label = @Translation("Embeddable parent id"),
 *   description = @Translation("A field containing a plain string value."),
 *   category = @Translation("Text"),
 *   default_widget = "string_textfield",
 *   default_formatter = "embeddable_parent_entity_reference",
 *   no_ui = TRUE
 * )
 */
class EmbeddableParentIdStringType extends StringItem {

}