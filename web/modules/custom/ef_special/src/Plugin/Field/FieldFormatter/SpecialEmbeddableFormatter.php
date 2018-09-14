<?php

namespace Drupal\ef_special\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef_special\SpecialEmbeddableInterface;
use Drupal\ef_special\SpecialEmbeddablePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @FieldFormatter(
 *   id = "field_special_embeddable_formatter",
 *   module = "ef_special",
 *   label = @Translation("Special embeddable formatter"),
 *   field_types = {
 *     "field_special_embeddable"
 *   }
 * )
 */
class SpecialEmbeddableFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\ef_special\SpecialEmbeddablePluginManager */
  protected $specialEmbeddablePluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, SpecialEmbeddablePluginManager $specialEmbeddablePluginManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->specialEmbeddablePluginManager = $specialEmbeddablePluginManager;
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.special_embeddable')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $specialEmbeddablePluginManager = $this->specialEmbeddablePluginManager;

    foreach ($items as $delta => $item) {
      $specialEmbeddableType = $item->value;
      $additionalOptions = $item->additional_options;

      /** @var SpecialEmbeddableInterface $specialEmbeddablePluginInstance */
      $specialEmbeddablePluginInstance = $specialEmbeddablePluginManager->createInstance($specialEmbeddableType);

      $elements[$delta] = $specialEmbeddablePluginInstance->render($additionalOptions);
    }

    return $elements;
  }

}
