<?php


namespace Drupal\ef\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * When an embeddable is part of a dependent relationship the entity type and
 * entity id is stored on the embeddable as two separate fields. This formatter
 * is associated with the id field, although it just goes back to the entity
 * and grabs the parent entity.
 *
 * @FieldFormatter(
 *   id = "embeddable_parent_entity_reference",
 *   label = @Translation("Rendered entity"),
 *   description = @Translation("Display the parent entity."),
 *   field_types = {
 *     "embeddable_parent_id"
 *   }
 * )
 */
class EmbeddableParentEntityReferenceFormatter extends FormatterBase implements ContainerFactoryPluginInterface {
  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'view_mode' => 'default',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // we have to display all view modes, unfortunately
    $all_view_modes = \Drupal::entityQuery('entity_view_mode')
      ->execute();

    $view_modes = [];

    foreach ($all_view_modes as $full_name_view_mode) {
      $vm = substr($full_name_view_mode, strpos($full_name_view_mode, '.') + 1);

      if (!isset($view_modes[$vm])) {
        $view_modes[$vm] = $vm;
      }
    }

    $elements['view_mode'] = [
      '#type' => 'select',
      '#options' => $view_modes,
      '#title' => t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $view_mode = $this->getSetting('view_mode');
    $summary[] = t('Rendered as @mode', ['@mode' => $view_mode]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\ef\EmbeddableInterface $embeddable */
    $embeddable = $items->getEntity();

    // Protect ourselves from recursive rendering.
    static $depth = [];
    if (!isset($depth[$embeddable->id()])) {
      $depth[$embeddable->id()] = 0;
    }
    $depth[$embeddable->id()]++;
    if ($depth[$embeddable->id()] > 20) {
      $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering parent entity embeddable @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $embeddable->bundle(), '@entity_id' => $embeddable->id()));
      return $elements;
    }

    $depth++;

    /** @var \Drupal\Core\Entity\ContentEntityInterface $parent_entity */
    $parent_entity = $embeddable->getParentEntity();

    if ($parent_entity) {
      $view_mode = $this->getSetting('view_mode');
      $entityTypeManager = \Drupal::service('entity_type.manager');
      $view_builder = $entityTypeManager->getViewBuilder($parent_entity->getEntityTypeId());
      $elements = $view_builder->view($parent_entity, $view_mode, $parent_entity->language()->getId());
    }

    return $elements;
  }

}