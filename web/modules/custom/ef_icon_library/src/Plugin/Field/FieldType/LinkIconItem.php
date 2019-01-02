<?php

namespace Drupal\ef_icon_library\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef_icon_library\LinkIconInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * Plugin implementation of the 'link_icon' field type.
 *
 * @FieldType(
 *   id = "link_icon",
 *   label = @Translation("Link with icon"),
 *   description = @Translation("Extension of the core link type to provide an icon choice."),
 *   default_widget = "link_icon_default",
 *   default_formatter = "link",
 *   constraints = {"LinkType" = {}, "LinkAccess" = {}, "LinkExternalProtocols" = {}, "LinkNotExistingInternal" = {}}
 * )
 */
class LinkIconItem extends LinkItem implements LinkIconInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return parent::defaultFieldSettings();;
//    return [
//      'title' => DRUPAL_OPTIONAL,
//      'link_type' => LinkItemInterface::LINK_GENERIC
//    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

//    $properties['uri'] = DataDefinition::create('uri')
//      ->setLabel(t('URI'));
//
//    $properties['title'] = DataDefinition::create('string')
//      ->setLabel(t('Link text'));
//
//    $properties['options'] = MapDataDefinition::create()
//      ->setLabel(t('Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return parent::schema($field_definition);

//    return [
//      'columns' => [
//        'uri' => [
//          'description' => 'The URI of the link.',
//          'type' => 'varchar',
//          'length' => 2048,
//        ],
//        'title' => [
//          'description' => 'The link text.',
//          'type' => 'varchar',
//          'length' => 255,
//        ],
//        'options' => [
//          'description' => 'Serialized array of options for the link.',
//          'type' => 'blob',
//          'size' => 'big',
//          'serialize' => TRUE,
//        ],
//      ],
//      'indexes' => [
//        'uri' => [['uri', 30]],
//      ],
//    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    return parent::fieldSettingsForm($form, $form_state);
//    $element = [];
//
//    $element['link_type'] = [
//      '#type' => 'radios',
//      '#title' => t('Allowed link type'),
//      '#default_value' => $this->getSetting('link_type'),
//      '#options' => [
//        static::LINK_INTERNAL => t('Internal links only'),
//        static::LINK_EXTERNAL => t('External links only'),
//        static::LINK_GENERIC => t('Both internal and external links'),
//      ],
//    ];
//
//    $element['title'] = [
//      '#type' => 'radios',
//      '#title' => t('Allow link text'),
//      '#default_value' => $this->getSetting('title'),
//      '#options' => [
//        DRUPAL_DISABLED => t('Disabled'),
//        DRUPAL_OPTIONAL => t('Optional'),
//        DRUPAL_REQUIRED => t('Required'),
//      ],
//    ];
//
//    return $element;
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function setValue($values, $notify = TRUE) {
//    // Treat the values as property value of the main property, if no array is
//    // given.
//    if (isset($values) && !is_array($values)) {
//      $values = [static::mainPropertyName() => $values];
//    }
//    if (isset($values)) {
//      $values += [
//        'options' => [],
//      ];
//    }
//    // Unserialize the values.
//    // @todo The storage controller should take care of this, see
//    //   SqlContentEntityStorage::loadFieldItems, see
//    //   https://www.drupal.org/node/2414835
//    if (is_string($values['options'])) {
//      $values['options'] = unserialize($values['options']);
//    }
//    parent::setValue($values, $notify);
//  }

}
