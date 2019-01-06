<?php

namespace Drupal\ef_icon_library\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\options\Plugin\Field\FieldFormatter\OptionsKeyFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'list_icon_key' formatter.
 *
 * @FieldFormatter(
 *   id = "list_icon_key",
 *   label = @Translation("Icon id"),
 *   field_types = {
 *     "list_icon"
 *   }
 * )
 */
class ListIconFormatter extends OptionsKeyFormatter implements ContainerFactoryPluginInterface {
  /**
   * @var \Drupal\ef_icon_library\IconLibraryInterface
   */
  protected $iconLibrary;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('ef.icon_library')
    );
  }

  /**
   * Constructs a new LinkFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param IconLibraryInterface $iconLibrary
   *   The icon library
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, IconLibraryInterface $iconLibrary) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );

    $this->iconLibrary = $iconLibrary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $icon = $this->iconLibrary->getIconInformation($item->value);
      $link_icon = $icon->id;

      $elements[$delta] = [
        '#markup' => $link_icon,
      ];
    }

    return $elements;
  }


}
