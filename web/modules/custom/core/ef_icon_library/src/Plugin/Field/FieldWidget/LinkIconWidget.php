<?php

namespace Drupal\ef_icon_library\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link_icon' widget.
 *
 * @FieldWidget(
 *   id = "link_icon_default",
 *   label = @Translation("Link with icon"),
 *   field_types = {
 *     "link_icon"
 *   }
 * )
 */
class LinkIconWidget extends LinkWidget implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\ef_icon_library\IconLibraryInterface
   */
  protected $iconLibrary;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, IconLibraryInterface $iconLibrary) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->iconLibrary = $iconLibrary;
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
      $container->get('ef.icon_library')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'available_link_styles' => ['button' => 'button', 'text' => 'text'],
        'available_positions' => ['before' => 'before', 'after' => 'after'],
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $icons = [
      '_none' => $this->t('-- No icon --'),
    ];

    $icons += $this->iconLibrary->getIconList();

    $field_name = $this->fieldDefinition->getName();

    $permitted_link_styles = array_filter($this->getSetting('available_link_styles'));

    $available_styles_list = array_intersect_key($this->getAvailableStyles(), $permitted_link_styles);

    $element['link_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Link style'),
      '#default_value' => isset($item->options['link_style']) ? $item->options['link_style'] : key($available_styles_list),
      '#maxlength' => 255,
      '#options' => $available_styles_list,
    ];

    if (count($available_styles_list) < 2) {
      $element['link_style']['#wrapper_attributes']['class'][] = 'visually-hidden';
    }

    $element['link_icon'] = [
      '#type' => 'select',
      '#title' => $this->t('Link icon'),
      '#default_value' => isset($item->options['link_icon']) ? $item->options['link_icon'] : '_none',
      '#maxlength' => 255,
      '#options' => $icons,
    ];

    $permitted_positions = array_filter($this->getSetting('available_positions'));

    $available_positions = array_intersect_key($this->getAvailablePositions(), $permitted_positions);

    $element['link_icon_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon position'),
      '#default_value' => isset($item->options['link_icon_position']) ? $item->options['link_icon_position'] : 'after',
      '#maxlength' => 255,
      '#options' => $available_positions,
      '#states' => [
        'invisible' => [
          ':input[name="' . sprintf('%s[%s][link_icon]', $field_name, $delta) . '"]' => [
            'value' => '_none',
          ],
        ],
      ],
    ];

    if (count($available_positions) < 2) {
      $element['link_icon_position']['#wrapper_attributes']['class'][] = 'visually-hidden';
    }

    return $element;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['available_link_styles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available link styles'),
      '#default_value' => $this->getSetting('available_link_styles'),
      '#required' => FALSE,
      '#options' => $this->getAvailableStyles(),
      '#description' => $this->t('Which style of link can the editor pick from? If you pick only one then the field will be visually hidden from the editor. If neither are picked then it is assumed that this is handled in the pattern template.'),
    ];

    $elements['available_positions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available icon positions'),
      '#default_value' => $this->getSetting('available_positions'),
      '#required' => FALSE,
      '#options' => $this->getAvailablePositions(),
      '#description' => $this->t('Which icon positions are available to the editor? If you pick only one then the field will be visually hidden from the editor. If neither are picked then it is assumed that this is handled in the pattern template.'),
    ];

    return $elements;
  }

  public function getAvailablePositions () {
    return [
      'before' => $this->t('Before text'),
      'after' => $this->t('After text'),
    ];
  }

  public function getAvailableStyles () {
    return [
      'button' => $this->t('Button'),
      'text' => $this->t('Text'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $available_link_styles = array_filter($this->getSetting('available_link_styles'));
    $available_positions = array_filter($this->getSetting('available_positions'));

    $available_link_styles_text = sizeof($available_link_styles) > 0 ? implode(', ', $available_link_styles) : 'none';
    $available_positions_text = sizeof($available_positions) ? implode(', ', $available_positions) : 'none';

    $summary[] = $this->t('Available link styles: @available_link_styles', ['@available_link_styles' => $available_link_styles_text]);
    $summary[] = $this->t('Available positions: @available_positions', ['@available_positions' => $available_positions_text]);

    return $summary;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    foreach ($values as $delta => $value) {
      if (isset($value["link_icon"]) && $value["link_icon"] != '_none') {
        $values[$delta]["options"]["link_icon"] = $value["link_icon"];
        $values[$delta]["options"]["link_icon_position"] = $value["link_icon_position"];
        $values[$delta]["options"]["link_style"] = $value["link_style"];
      }
    }

    return $values;
  }

}
