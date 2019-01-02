<?php

namespace Drupal\ef_icon_library\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link icon' formatter.
 *
 * @FieldFormatter(
 *   id = "link_icon",
 *   label = @Translation("Link with icon"),
 *   field_types = {
 *     "link_icon"
 *   }
 * )
 */
class LinkIconFormatter extends LinkFormatter {
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
      $container->get('path.validator'),
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
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PathValidatorInterface $path_validator, IconLibraryInterface $iconLibrary) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings,
      $path_validator
    );

    $this->iconLibrary = $iconLibrary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $parent_element = parent::viewElements($items, $langcode);

    $element = [];

    foreach ($parent_element as $delta => $parent_element_item) {
      $link_icon = '';

      if ($parent_element_item['#options']['link_icon']) {
        $icon = $this->iconLibrary->getIconInformation($parent_element_item['#options']['link_icon']);
        $link_icon = $icon->id;
      }

      /** @var \Drupal\Core\Url $url */
      $url = $parent_element_item['#url'];

      switch ($parent_element_item['#options']['link_style']) {
        case 'button':
          $element[] = [
            '#type' => 'pattern',
            '#id' => 'button_icon',
            '#fields' => [
              'button_text' => $parent_element_item['#title'],
              'button_icon_name' => $link_icon,
              'button_icon_position' => $parent_element_item['#options']['link_icon_position'] ? $parent_element_item['#options']['link_icon_position'] : '',
            ],
          ];
          break;

        case 'text':
          $element[] = [
            '#type' => 'pattern',
            '#id' => 'link_icon',
            '#fields' => [
              'link_icon_text' => $parent_element_item['#title'],
              'link_icon_url' => $url->toString(),
              'link_icon_name' => $link_icon,
            ],
          ];
          break;
      }


    }

    return $element;
  }


}
