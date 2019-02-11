<?php

namespace Drupal\ef_special\Plugin\Field\FieldType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 *
 * @FieldType(
 *   id = "field_special_embeddable",
 *   label = @Translation("Special embeddable"),
 *   module = "irc_embeddable",
 *   description = @Translation("A field that presents all the special embeddables that available."),
 *   default_widget = "field_special_embeddable_widget",
 *   default_formatter = "field_special_embeddable_formatter"
 * )
 */
class SpecialEmbeddableField extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => TRUE,
        ],
        'additional_options' => [
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Special embeddable type'))
      ->setRequired(TRUE);

    $properties['additional_options'] = DataDefinition::create('any')
      ->setLabel(t('Additional options'));

    return $properties;
  }

}
