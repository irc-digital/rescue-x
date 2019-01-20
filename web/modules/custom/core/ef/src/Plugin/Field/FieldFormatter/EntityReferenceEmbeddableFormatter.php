<?php

namespace Drupal\ef\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\ef\EmbeddableViewBuilderInterface;
use Drupal\ef\EmbeddableViewModeVisibilityServiceInterface;
use Drupal\ef\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityField;
use Drupal\ef\Plugin\EmbeddableViewModeVisibilityInterface;
use Drupal\ef\Plugin\Field\FieldType\EntityReferenceEmbeddableItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_embeddable_view",
 *   label = @Translation("Rendered embeddable"),
 *   description = @Translation("Display the referenced embeddable entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_embeddable"
 *   }
 * )
 */
class EntityReferenceEmbeddableFormatter extends EntityReferenceFormatterBase  implements ContainerFactoryPluginInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /** @var  \Drupal\ef\EmbeddableReferenceModeInterface */
  protected $embeddableReferenceMode;

  /** @var \Drupal\ef\EmbeddableViewModeVisibilityServiceInterface */
  protected $embeddableViewModeVisibilityService;

  /**
   * Constructs a EntityReferenceEmbeddableFormatter instance.
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
   *   Any third party settings settings.
   * @param LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param EmbeddableReferenceModeInterface $embeddable_reference_mode
   *   The embeddable reference mode
   * @param EmbeddableViewModeVisibilityServiceInterface $embeddableViewModeVisibilityService
   *   The embeddable view mode service
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository, EmbeddableReferenceModeInterface $embeddable_reference_mode, EmbeddableViewModeVisibilityServiceInterface $embeddableViewModeVisibilityService) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->embeddableReferenceMode = $embeddable_reference_mode;
    $this->embeddableViewModeVisibilityService = $embeddableViewModeVisibilityService;
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
      $container->get('logger.factory'),
      $container->get('entity_display.repository'),
      $container->get('ef.embeddable_reference_mode'),
      $container->get('ef.view_mode_visibility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'embedding_options' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $usage_context = $this->getFieldSetting('ef_view_mode_visibility_usage_context');

    if (is_null($usage_context)) {
      $usage_context = EmbeddableViewModeVisibilityField::class;
    }

    if ($usage_context != EmbeddableViewModeVisibilityField::class) {
      $embedding_options = $this->getSetting('embedding_options');

      $view_mode = isset($embedding_options['view_mode']) ? $embedding_options['view_mode'] : 'default';
      $options = isset($embedding_options['options']) ? $embedding_options['options'] : [];
      $mode = isset($embedding_options['mode']) ? $embedding_options['mode'] : $this->embeddableReferenceMode->getDefaultMode();

      $handler = $this->getFieldSetting('handler_settings');
      $target_bundles = $handler['target_bundles'];

      $enabled_bundles = array_filter($target_bundles);
      $bundle = key($enabled_bundles);

      $elements['embedding_options'] = [
        '#weight' => 100,
        '#visibility' => $usage_context,
        '#type' => 'embedding_options',
        '#prefix' => '<div id="embedding-options-wrappers">',
        '#suffix' => '</div>',
        '#bundle' => $bundle,
        '#default_value' => [
          'view_mode' => $view_mode,
          'options' => $options,
          'mode' => $mode,
        ],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $embeddable) {
      static $depth = [];
      if (!isset($depth[$embeddable->id()])) {
        $depth[$embeddable->id()] = 0;
      }
      $depth[$embeddable->id()]++;
      if ($depth[$embeddable->id()] > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering embeddable @entity_id. Aborting rendering.', array('@entity_id' => $embeddable->id()));
        return $elements;
      }

      // handle options
      $view_mode_option = $this->getFieldSetting('view_mode_option');

      $usage_context = $this->getFieldSetting('ef_view_mode_visibility_usage_context');

      if (is_null($usage_context)) {
        $usage_context = EmbeddableViewModeVisibilityField::class;
      }

      if ($usage_context != EmbeddableViewModeVisibilityField::class) {
        $embedding_options = $this->getSetting('embedding_options');
        $view_mode = isset($embedding_options['view_mode']) ? $embedding_options['view_mode'] : 'default';
        $options = isset($embedding_options['options']) ? $embedding_options['options'] : [];
        $mode = isset($embedding_options['mode']) ? $embedding_options['mode'] : $this->embeddableReferenceMode->getDefaultMode();
      } else {
        $options = isset($items[$delta]->options) && is_array($items[$delta]->options) ? $items[$delta]->options : [];
        $mode = $items[$delta]->mode;

        if ($view_mode_option == EntityReferenceEmbeddableItem::VIEW_MODE_SET_BY_EDITOR) {
          $view_mode = isset($items[$delta]->view_mode) && strlen($items[$delta]->view_mode) > 0 ? $items[$delta]->view_mode : 'default';
        } else {
          $view_mode = $view_mode_option;
        }
      }


      $elements[$delta] = [
        '#type' => 'embeddable',
        '#embeddable' => $embeddable,
        '#view_mode' => $view_mode,
        '#options' => $options,
        '#mode' => $mode,
      ];

      $this->processHeader ($elements[$delta], $items[$delta], $langcode);

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$embeddable->isNew() && $embeddable->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += array('resource' => $embeddable->toUrl()->toString());
      }
      $depth = 0;
    }

    return $elements;
  }

  protected function processHeader (&$element, EntityReferenceEmbeddableItem $item, $langcode) {
    // handle header title and description
    $can_edit_title_field = $this->getFieldSetting('editable_header_title');
    $default_header_title = $this->getFieldSetting('default_header_title');

    $header_title_value = NULL;

    $header_title_value = $default_header_title;

    if ($can_edit_title_field) {
      if (isset($item->header_title) && strlen($item->header_title) > 0) {
        $header_title_value = $item->header_title;
      }
    }

    if ($header_title_value && strlen($header_title_value) > 0) {
      $element['#header_title'] = $header_title_value;

      $can_edit_description_field = $this->getFieldSetting('editable_header_description');

      if ($can_edit_description_field && isset($item->header_description) && strlen($item->header_description) > 0) {
        $element['#header_description'] = $item->header_description;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getType() == 'entity_reference_embeddable';
  }

}
