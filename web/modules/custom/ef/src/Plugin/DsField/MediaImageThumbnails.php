<?php

namespace Drupal\ef\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A DS field that renders the teaser images in the most common format. Designed
 * to encourage editors to consider cropping images that look poor at any of
 * these crops
 *
 * @DsField(
 *   id = "media_image_thumbnails",
 *   entity_type = "media",
 *   title = "Sample thumbnails",
 *   ui_limit = {
 *     "ef_image|thumbnails",
 *   }
 * )
 *
 */
class MediaImageThumbnails extends DsFieldBase {
  /**
   * The EntityDisplayRepository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'image_styles' => [
          'image.style.square_1x1_117px_wide',
          'image.style.super_widescreen_21x9_273px_wide',
          'image.style.widescreen_16x9_208px_wide'
        ],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $settings = [];

    $image_style_storage = $this->entityTypeManager->getStorage('image_style');

    $all_image_styles = $image_style_storage->loadMultiple();
    uasort($all_image_styles, function ($style_one, $style_two) {
      return strcmp($style_one->label(),$style_two->label());
    });

    $displayed_style_list = [];

    foreach ($all_image_styles as $machine_name => $image_style) {
      $displayed_style_list[$machine_name] = $image_style->label();
    }

    $settings['image_styles'] = [
      '#title' => t('Image styles'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#default_value' => $config['image_styles'] ?: NULL,
      '#required' => TRUE,
      '#options' => $displayed_style_list,
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $summary = [];

    $config = $this->getConfiguration();

    if (isset($config['image_styles'])) {
      $image_style_storage = $this->entityTypeManager->getStorage('image_style');

      $image_styles = $image_style_storage->loadMultiple($settings['image_styles']);

      if (count($image_styles) > 0) {
        $styles = [];
        foreach ($image_styles as $image_style_entry) {
          $styles[] = $image_style_entry->label();
        }
        $summary[] =t('Teaser image styles: @image_styles', ['@image_styles' => implode(', ', $styles)]);
      } else {
        $summary[] = t('Select some thumbnail image styles.');
      }
    }
    else {
      $summary[] = t('Select some thumbnail image styles.');
    }

    return $summary + parent::settingsSummary($settings);

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();

    $image_entity = $entity->field_ef_image->entity;

    $config = $this->getConfiguration();

    if (isset($config['image_styles'])) {
      return [
        '#theme' => 'media_image_thumbnails',
        '#image_path' => $image_entity->uri->value,
        '#image_styles' => $config['image_styles'],
      ];

    }
  }
}
