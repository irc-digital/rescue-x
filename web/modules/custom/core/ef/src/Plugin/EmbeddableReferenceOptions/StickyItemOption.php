<?php

namespace Drupal\ef\Plugin\EmbeddableReferenceOptions;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableReferenceOptions;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a options plugin for capturing an item that should remain sticky
 * at the top of a list
 *
 * @EmbeddableReferenceOptions(
 *   id = "embeddable_sticky_option",
 *   label = @Translation("Sticky at top of list")
 * )
 */
class StickyItemOption extends EmbeddableReferenceOptionsPluginBase implements ContainerFactoryPluginInterface {
  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }


  /**
   * @inheritdoc
   */
  function buildForm($embeddable_bundle, array $values) {

    $formElement = [];

    $value = NULL;

    if (isset($values['sticky_id'])) {
      $value = $this->entityTypeManager->getStorage($this->configuration['entity_type'])->load($values['sticky_id']);
    }

    $formElement['sticky_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => $this->configuration['entity_type'],
      '#title' => $this->t('Sticky item'),
      '#description' => $this->t('You may set one item to remain fixed to the top of the list.'),
      '#default_value' => $value,
      '#size' => 90,
      '#required' => FALSE,
    ];

    return $formElement;
  }

  /**
   * @inheritdoc
   */
  function getOptionValue ($options) {
    return isset($options['sticky_id']) && strlen($options['sticky_id'] > 0) ? $options['sticky_id'] : NULL;
  }

  public function defaultConfiguration() {
    return [
      'entity_type' => 'node',
    ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $definitions = \Drupal::entityTypeManager()->getDefinitions();

    $types = [];

    /** @var \Drupal\Core\Entity\ContentEntityTypeInterface $definition */
    foreach ($definitions as $definition) {
      $dataTable = $definition->getDataTable();

      if ($dataTable) {
        $types[$definition->id()] = $definition->getLabel();
      }
    }

    $form['entity_type'] = [
      '#type' => 'select',
      '#options' => $types,
      '#title' => $this->t('Entity type'),
      '#description' => $this->t('What type of entities are in the list?'),
      '#default_value' => $this->configuration['entity_type'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->submitConfigurationForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $key = $form['#parents'];
    array_push($key, 'entity_type');
    $entity_type = $form_state->getValue($key);
    $this->configuration['entity_type'] = $form_state->getValue($entity_type);
  }

}
