<?php

namespace Drupal\ef\Plugin\Field\FieldType;

use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\ef\EmbeddableMode;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\ef\Entity\EmbeddableType;
use Drupal\ef\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityField;

/**
 * Defines the 'entity_reference_embeddable' entity field type.
 *
 * @FieldType(
 *   id = "entity_reference_embeddable",
 *   label = @Translation("Embeddable"),
 *   description = @Translation("An entity field containing an entity reference to an embeddable."),
 *   category = @Translation("Reference"),
 *   no_ui = FALSE,
 *   class = "\Drupal\ef\Plugin\Field\FieldType\EntityReferenceEmbeddableItem",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 *   default_formatter = "entity_reference_embeddable_view",
 *   default_widget = "entity_reference_embeddable_widget"
 * )
 */
class EntityReferenceEmbeddableItem extends EntityReferenceItem {

  const VIEW_MODE_SET_BY_EDITOR = 'set_by_editor';

  public static function getPreconfiguredOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => 'embeddable',
    ] + parent::defaultStorageSettings();
  }

  public static function defaultFieldSettings() {
    return [
      'editable_header_title' => FALSE,
      'default_header_title' => '',
      'editable_header_description' => FALSE,
      'view_mode_option' => self::VIEW_MODE_SET_BY_EDITOR,
      'dependent_embeddable' => FALSE,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['view_mode'] = DataDefinition::create('string')
      ->setLabel(t('Variation'));

    $properties['header_title'] = DataDefinition::create('string')
      ->setRequired(FALSE)
      ->setLabel(t('Header title'));

    $properties['header_description'] = DataDefinition::create('string')
      ->setRequired(FALSE)
      ->setLabel(t('Header description'));

    $properties['header_description_format'] = DataDefinition::create('filter_format')
      ->setRequired(FALSE)
      ->setLabel(t('Header description text format'));

    $properties['mode'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Mode'));

    $properties['options'] = DataDefinition::create('any')
      ->setLabel(t('Options'));

    return $properties;
  }


  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    // the view mode
    $schema['columns']['view_mode'] = [
      'description' => 'The view mode.',
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => 255,
    ];

    $schema['columns']['header_title'] = [
      'description' => 'The header title.',
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => 255,
    ];

    $schema['columns']['header_description'] = [
      'description' => 'The header description.',
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ];

    $schema['columns']['header_description_format'] = [
      'description' => 'The header description text format.',
      'type' => 'varchar_ascii',
      'not null' => FALSE,
      'length' => '255',
    ];

    $schema['columns']['mode'] = [
      'description' => 'The embeddable mode.',
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => 32,
    ];

    $schema['columns']['options'] = [
      'description' => 'Options blob.',
      'type' => 'blob',
      'not null' => FALSE,
      'size' => 'big',
      'serialize' => TRUE,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = parent::storageSettingsForm($form, $form_state, $has_data);

    $element['target_type']['#access'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);

    /** @var FieldConfigInterface $field */
    $field = $form_state->getFormObject()->getEntity();

    $wrapper_id = 'embeddable-options';

    $form['embeddable'] = [
      '#type' => 'details',
      '#title' => t('Embeddable options'),
      '#open' => TRUE,
      '#tree' => TRUE,
      '#process' => [[get_class($this), 'formProcessMergeParent']],
      '#element_validate' => [[get_class($this), 'entityReferenceEmbeddableFieldSettingsFormValidate']],
      '#attributes' => [
        'id' => $wrapper_id,
      ],
    ];

    $handler_settings = $field->getSetting('handler_settings');

    $target_bundles = $form_state->getValue(['handler_settings', 'target_bundles'], isset($handler_settings['target_bundles'])) ? $handler_settings['target_bundles'] : [];

    $enabled_bundles = !is_null($target_bundles) ? array_filter($target_bundles) : [];

    $one_bundle_set = !(empty($target_bundles) || count($enabled_bundles) > 1);

    $is_dependent_embeddable = $form_state->getValue(['settings', 'dependent_embeddable'], $field->getSetting('dependent_embeddable'));

    // dependent embeddable option is only for single select fields
    $cardinality = $field->getFieldStorageDefinition()->getCardinality();
    if ($cardinality === 1) {
      $form['embeddable']['dependent_embeddable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Dependent embeddable'),
        '#disabled' => !$one_bundle_set,
        '#default_value' => $is_dependent_embeddable,
        '#description' => $this->t("When checked, when this entity is created an embeddable will be created. This dependent embeddable cannot be selected by the editor. When this is checked, only a single embeddable type is permitted."),
        '#ajax' => [
          'event' => 'change',
          'callback' => [$this, 'bundleListChanged'],
          'wrapper' => $wrapper_id,
        ],
      ];
    }

    $form['handler']['handler_settings']['target_bundles']['#ajax'] = [
      'event' => 'change',
      'callback' => [$this, 'bundleListChanged'],
      'wrapper' => $wrapper_id,
    ];

    $form['handler']['handler_settings']['target_bundles']['#disabled'] = $is_dependent_embeddable;

    $form['embeddable']['handler_settings'] = $form['handler']['handler_settings'];
    $form['embeddable']['handler_settings']['sort']['#access'] = FALSE;
    $form['embeddable']['handler_settings']['auto_create']['#access'] = FALSE;
    $form['embeddable']['handler_settings']['auto_create_bundle']['#access'] = FALSE;
    unset($form['handler']['handler_settings']);
    $form['handler']['#access'] = FALSE;

    $view_mode_options = [
      self::VIEW_MODE_SET_BY_EDITOR => $this->t ('Set by editor'),
    ];

    $view_mode_option = $form_state->getValue(['settings', 'view_mode_option'], $field->getSetting('view_mode_option'));;
    if ($one_bundle_set) {
      /** @var \Drupal\ef\EmbeddableViewModeVisibilityServiceInterface */
      $embeddableViewModeVisibilityService = \Drupal::service('ef.view_mode_visibility');
      $view_modes = $embeddableViewModeVisibilityService->getVisibleViewModes(key($enabled_bundles), EmbeddableViewModeVisibilityField::class);

      $view_mode_options += $view_modes;
    } else {
      $view_mode_option = self::VIEW_MODE_SET_BY_EDITOR;
    }

    $form['embeddable']['view_mode_option'] = [
      '#type' => 'select',
      '#options' => $view_mode_options,
      '#title' => $this->t('View mode option'),
      '#description' => $this->t('How is the view mode set or what is the view mode? A specific view mode can only be set if there is only a single bundle selected.'),
      '#default_value' => $view_mode_option,
      '#disabled' => !$one_bundle_set,
      '#required' => TRUE,
    ];

    $form['embeddable']['default_header_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default header title'),
      '#default_value' => $field->getSetting('default_header_title'),
      '#description' => $this->t('The default header title. If the editor is able to change the title then this just becomes a placeholder for them. If they cannot, then this is used as the title for the embeddable header section. This should be written in the default language of the site and it will be passed through the string translator when output.'),
    ];

    $form['embeddable']['editable_header_title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Editable header title'),
      '#default_value' => $field->getSetting('editable_header_title'),
      '#description' => $this->t('Can the editor change the header title?'),
    ];

    $form['embeddable']['editable_header_description'] = [
      '#type' => 'checkbox',
      '#states' => [
        'visible' => [
          ':input[name="settings[editable_header_title]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
      '#title' => $this->t('Editable header description'),
      '#default_value' => $field->getSetting('editable_header_description'),
      '#description' => $this->t('Can the editor provide a description that is used in the header section?'),
    ];

    return $form;
  }

  public static function entityReferenceEmbeddableFieldSettingsFormValidate(array $form, FormStateInterface $form_state) {
    $field = $form_state->getFormObject()->getEntity();

    $dependent_embeddable = $field->getSetting('dependent_embeddable');
    if ($dependent_embeddable) {
      $handler = $field->getSetting('handler_settings');
      $target_bundles = $handler['target_bundles'];

      $enabled_bundles = array_filter($target_bundles);

      if (empty($target_bundles) || count($enabled_bundles) > 1) {
        $form_state->setErrorByName('settings][handler_settings', t('You must select a single embeddable type when you have marked the field as a dependent embeddable.'));
        return;
      }

      $target_bundle_name = key($enabled_bundles);
      // ensure the selected bundle has been marked to support having dependent embeddables
      $target_bundle = EmbeddableType::load($target_bundle_name);

      if (!$target_bundle->isDependentType()) {
        $form_state->setErrorByName('settings][handler_settings', t('The bundle type is not marked as being a dependent embeddable. Please adjust this on the bundle type config screen and try again.'));
      }
    }
  }

  public function bundleListChanged($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -1);

    $element = NestedArray::getValue($form, $key);

    return $element;
  }

}
