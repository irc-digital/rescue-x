<?php

namespace Drupal\ef_patterns\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for our image attribute fields
 */
abstract class BaseImageAttributesField extends DsFieldBase {

  /**
   * The EntityDisplayRepository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LinkGeneratorInterface $link_generator, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->linkGenerator = $link_generator;
    $this->currentUser = $current_user;

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
      $container->get('entity.manager'),
      $container->get('link_generator'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getEntityWithImageField();
    $image_field_name = $this->getImageFieldName();

    $output = '';

    if (!is_null($entity)) {
      if (in_array($config['field']['field_attribute'], ['alt'])) {
        $output = $entity->{$image_field_name}->{$config['field']['field_attribute']};
      } else {
        $image = $entity->{$image_field_name}->entity;

        if ($image) {
          $image_uri = $image->uri->value;
          $output = ef_patterns_get_responsive_image_element ($config['responsive_image_style'], $config['field']['field_attribute'], $image_uri);
        }
      }
    }

    return [
      '#markup' => $output,
      '#ef_ds_custom_field_element' => TRUE,
    ];
  }

  abstract protected function getEntityWithImageField();

  abstract protected function getImageFieldName();

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'responsive_image_style' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $settings = [];

    if (in_array($config['field']['field_attribute'], $this->getAttributesThatNeedResponsiveStyle())) {
      $responsive_image_style_storage = $this->entityTypeManager->getStorage('responsive_image_style');

      $responsive_image_options = [];
      $responsive_image_styles = $responsive_image_style_storage->loadMultiple();
      if ($responsive_image_styles && !empty($responsive_image_styles)) {
        uasort($responsive_image_styles, function ($style_one, $style_two) {
          return strcmp($style_one->label(),$style_two->label());
        });

        foreach ($responsive_image_styles as $machine_name => $responsive_image_style) {
          if ($responsive_image_style->hasImageStyleMappings()) {
            $responsive_image_options[$machine_name] = $responsive_image_style->label();
          }
        }
      }

      $settings['responsive_image_style'] = [
        '#title' => t('Responsive image style'),
        '#type' => 'select',
        '#default_value' => $config['responsive_image_style'] ?: NULL,
        '#required' => TRUE,
        '#options' => $responsive_image_options,
        '#description' => [
          '#markup' => $this->linkGenerator->generate($this->t('Configure Responsive Image Styles'), new Url( 'entity.responsive_image_style.collection')),
          '#access' => $this->currentUser->hasPermission('administer responsive image styles'),
        ],
      ];

    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $summary = [];

    $config = $this->getConfiguration();

    if (in_array($config['field']['field_attribute'], $this->getAttributesThatNeedResponsiveStyle())) {
      if (isset($settings['responsive_image_style'])) {
        $responsive_image_style_storage = $this->entityTypeManager->getStorage('responsive_image_style');

        $responsive_image_style = $responsive_image_style_storage->load($settings['responsive_image_style']);

        if ($responsive_image_style) {
          $summary[] = t('Responsive image style: @responsive_image_style', ['@responsive_image_style' => $responsive_image_style->label()]);
        } else {
          $summary[] = t('Select a responsive image style.');
        }
      }
      else {
        $summary[] = t('Select a responsive image style.');
      }
    }

    return $summary + parent::settingsSummary($settings);

  }

  protected function getAttributesThatNeedResponsiveStyle () {
    return ['sources','srcset', 'sizes','fallback_uri'];
  }
}
