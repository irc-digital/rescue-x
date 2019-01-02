<?php

namespace Drupal\ef\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\EmbeddableMode;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\ef\Plugin\Field\FieldType\EntityReferenceEmbeddableItem;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Plugin implementation to support embeddable content
 *
 * @FieldWidget(
 *   id = "entity_reference_embeddable_widget",
 *   label = @Translation("Embeddable autocomplete"),
 *   description = @Translation("An embeddable content form widget."),
 *   field_types = {
 *     "entity_reference_embeddable"
 *   }
 * )
 */
class EmbeddableContentWidget extends EntityReferenceAutocompleteWidget implements ContainerFactoryPluginInterface  {
  /** @var \Drupal\Core\Entity\EntityStorageInterface */
  protected $embeddableStorage;

  /** @var  EmbeddableReferenceModeInterface */
  protected $embeddableReferenceMode;

  /** @var \Drupal\Core\Language\LanguageManagerInterface */
  protected $languageManager;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $embeddable_storage, EmbeddableReferenceModeInterface $embeddableReferenceMode, LanguageManagerInterface $languageManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->embeddableStorage = $embeddable_storage;
    $this->embeddableReferenceMode = $embeddableReferenceMode;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('embeddable'),
      $container->get('ef.embeddable_reference_mode'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'show_edit_button' => TRUE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['show_edit_button'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show edit button'),
      '#default_value' => $this->getSetting('show_edit_button'),
      '#description' => $this->t('Should the edit button be presented on the editorial embeddable widget form?'),
    ];

    $dependent_embeddable = $this->getFieldSetting('dependent_embeddable');

    if ($dependent_embeddable) {
      $element['match_operator']['#access'] = FALSE;
      $element['size']['#access'] = FALSE;
      $element['placeholder']['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $dependent_embeddable = $this->getFieldSetting('dependent_embeddable');

    if (!$dependent_embeddable) {
      $summary = parent::settingsSummary();
    }

    $show_edit_button = $this->getSetting('show_edit_button');

    $summary[] = $show_edit_button ? $this->t('Show edit button') : $this->t('Hide edit button');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = $this->prepareFormElement ($items, $delta, $element, $form, $form_state);
    $element = $this->embeddingOptionsFormElement ($items, $delta, $element, $form, $form_state);
    $element = $this->titleDescriptionFormElement ($items, $delta, $element, $form, $form_state);
    //$element = $this->addWidgetStyles ($element);

    return $element;
  }

  protected function prepareFormElement (FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $parent_element = parent::formElement($items, $delta, [], $form, $form_state);
    $parent_element_key = key ($parent_element);

    $wrapper_id = $this->prepareElement ($element, $delta);

    $dependent_embeddable = $this->getFieldSetting('dependent_embeddable');

    if ($dependent_embeddable) {
      $parent_element[$parent_element_key]['#access'] = FALSE;
    } else {
      $parent_element[$parent_element_key] += [
        '#weight' => 0,
        '#ajax' => [
          'event' => 'autocompleteclose change',
          'callback' => [$this, 'ajaxFunctionAfterAutocomplete'],
          'wrapper' => $wrapper_id,
          'effect' => 'fade',
        ],
      ];
    }

    $element[$parent_element_key] = $parent_element[$parent_element_key];

    return $element;
  }

  protected function embeddingOptionsFormElement (FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $embeddables = $this->getEmbeddables($items, $form_state);

    // the options
    if (isset($embeddables[$delta]) && $embeddables[$delta]) {
      /** @var EmbeddableInterface $embeddable */
      $embeddable = $embeddables[$delta];

      $lang_code = $this->languageManager->getCurrentLanguage()->getId();

      $translation_exists_access = TRUE;

      if ($embeddable->hasTranslation($lang_code)) {
        $embeddable = $embeddable->getTranslation($lang_code);
      } else {
        $translation_exists_access = FALSE;
      }

      $view_mode_option = $this->getFieldSetting('view_mode_option');

      $show_edit_button = $this->getSetting('show_edit_button');

      if ($show_edit_button) {
        $edit_form = $embeddable->toUrl('edit-form');

        $element['subform']['edit_link'] = [
          '#title' => $this->t('Edit'),
          '#type' => 'link',
          '#url' => $edit_form,
          '#access' => $translation_exists_access && $edit_form->access(),
          '#attributes' => [
            'class' => ['edit-embeddable-button', 'form-item', 'button', 'button--secondary', 'button--small'],
            'target' => '_blank',
          ],
        ];
      }

      if ($view_mode_option == EntityReferenceEmbeddableItem::VIEW_MODE_SET_BY_EDITOR) {
        $view_mode = $this->getElementValue (['subform', 'embedding_options', 'view_mode'], $items, $delta, $element, $form_state, NULL);
      } else {
        $view_mode = $view_mode_option;
      }

      $options = isset($items[$delta]->options) ? $items[$delta]->options : [];

      $mode = $this->getElementValue (['subform', 'embedding_options', 'mode'], $items, $delta, $element, $form_state, $this->embeddableReferenceMode->getDefaultMode());

      $element['subform']['embedding_options'] = [
        '#weight' => 100,
        '#type' => 'embedding_options',
        '#bundle' => $embeddable->bundle(),
        '#access' => $translation_exists_access,
        '#view_mode_editable' => $view_mode_option == EntityReferenceEmbeddableItem::VIEW_MODE_SET_BY_EDITOR,
        '#default_value' => [
          'view_mode' => $view_mode,
          'options' => $options,
          'mode' => $mode,
        ],
      ];
    }

    return $element;
  }

  protected function titleDescriptionFormElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $embeddables = $this->getEmbeddables($items, $form_state);

    $can_edit_title_field = $this->getFieldSetting('editable_header_title');

    if ($can_edit_title_field && isset($embeddables[$delta]) && $embeddables[$delta]) {

      $header_title = $this->getElementValue (['header', 'header_title'], $items, $delta, $element, $form_state, '');

      $default_header_title = $this->getFieldSetting('default_header_title');

      // add in the title, description
      $element['subform']['header'] = [
        '#type' => 'details',
        '#weight' => 150,
        '#title' => $this->t('Section title and description'),
      ];

      $element['subform']['header']['header_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#default_value' => $header_title,
        '#placeholder' => $default_header_title,
      ];

      $can_edit_description_field = $this->getFieldSetting('editable_header_description');

      if ($can_edit_description_field) {


        $header_description = $this->getElementValue (['subform', 'header', 'header_description'], $items, $delta, $element, $form_state, '');
        $header_description_format = $this->getElementValue (['subform', 'header', 'header_description_format'], $items, $delta, $element, $form_state, 'plain_text');

        $element['subform']['header']['header_description'] = [
          '#type' => 'text_format',
          '#format' => $header_description_format,
          '#title' => $this->t('Description'),
          '#default_value' => $header_description,
        ];
      } else {
        $element['subform']['header']['#type'] = 'container';
        $element['subform']['header']['header_title']['#title'] =$this->t('Section title');
      }

    }

    return $element;
  }

//  public function addWidgetStyles (array $element) {
//    $element['#attached']['library'][] = 'ef/embeddable-widget';
//    return $element;
//  }

  protected function getElementValue ($key, FieldItemListInterface $items, $delta, array $element, FormStateInterface $form_state, $default = NULL) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $element['#field_parents'];

    $element_key = $parents;
    $element_key[] = $field_name;
    $element_key[] = $delta;
    $element_key = array_merge ($element_key, is_array($key) ? $key : [$key]);

    $last_key = end ($element_key);

    $value = $form_state->getValue($element_key, !is_null($items[$delta]->$last_key) ? $items[$delta]->$last_key : $default);

    return $value;
  }

  /**
   *
   * This is somewhat like the autocomplete widget's referencedEntities, but it
   * supports information coming from the form state
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  protected function getEmbeddables (FieldItemListInterface $items, FormStateInterface $form_state) {

    // if we have values in the form state, we should use those
    $form_values = $form_state->getValues();
    $field_name = $this->fieldDefinition->getName();

    if ($form_values && isset($form_values[$field_name]) && is_array($form_values[$field_name])) {
      $result = [];

      foreach ($form_values[$field_name] as $key => $value) {
        if ($key === 'add_more') {
          continue;
        }
        $embeddable = NULL;

        if (isset($value['target_id'])) {
          $embeddable = $this->embeddableStorage->load($value['target_id']);
        }

        $result[$key] = $embeddable;
      }
    } else {
      $result = $items->referencedEntities();
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      if (isset($value['subform'])) {
        if (isset($value['subform']['embedding_options']['mode'])) {
          $values[$key]['mode'] = $value['subform']['embedding_options']['mode'];
        }

        if (isset($value['subform']['embedding_options']['view_mode'])) {
          $values[$key]['view_mode'] = $value['subform']['embedding_options']['view_mode'];
        }

        if (isset($value['subform']['embedding_options']['options'])) {
          $values[$key]['options'] = $value['subform']['embedding_options']['options'];
        }

        if (isset($value['subform']['header']['header_title'])) {
          $values[$key]['header_title'] = $value['subform']['header']['header_title'];
        }

        if (isset($value['subform']['header']['header_description'])) {
          $values[$key]['header_description'] = $value['subform']['header']['header_description']['value'];
          $values[$key]['header_description_format'] = $value['subform']['header']['header_description']['format'];
        }

        unset($values[$key]['subform']);
      }
    }

    $values = parent::massageFormValues($values, $form, $form_state);

    return $values;
  }

  protected function prepareElement (array &$element, $delta) {
    $parents = $element['#field_parents'];
    $field_name = $this->fieldDefinition->getName();

    $container_type = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality() === 1 ? 'fieldset' : 'container';

    $element_parents = $parents;
    $element_parents[] = $field_name;
    $element_parents[] = $delta;
    $element_parents[] = 'subform';

    $id_prefix = implode('-', array_merge($parents, array($field_name, $delta)));
    $wrapper_id = $id_prefix . '-item-wrapper';

    $element += [
      '#type' => $container_type,
      'subform' => [
        '#weight' => 100,
        '#type' => 'container',
        '#parents' => $element_parents,
        '#prefix' => '<div id="' . $wrapper_id . '" class="embeddable-subform">',
        '#suffix' => '</div>',
      ],
    ];

    return $wrapper_id;
  }

  public function ajaxFunctionAfterAutocomplete($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -1);

    $element = NestedArray::getValue($form, $key);

    return $element['subform'];
  }
}
