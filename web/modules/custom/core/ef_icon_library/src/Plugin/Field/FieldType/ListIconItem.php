<?php


namespace Drupal\ef_icon_library\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\options\Plugin\Field\FieldType\ListItemBase;

/**
 * Plugin implementation of the 'list_icon' field type.
 *
 * @FieldType(
 *   id = "list_icon",
 *   label = @Translation("List (icon)"),
 *   description = @Translation("This field stores the selection of an icon library icon."),
 *   category = @Translation("Text"),
 *   default_widget = "options_select",
 *   default_formatter = "list_icon_key",
 * )
 */
class ListIconItem extends ListItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
        'allowed_values_function' => 'ef_icon_library_allowed_icon_values_function',
      ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Icon name'))
      ->addConstraint('Length', ['max' => 100])
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 100,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  protected function allowedValuesDescription() {
    return '';
  }


  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    if (mb_strlen($option) > 100) {
      return t('Allowed values list: each icon name must be a string at most 100 characters long.');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected static function castAllowedValue($value) {
    return (string) $value;
  }

}