<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef_reach_through_content\Entity\ReachThroughType;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeTypeInterface;

class ReachThroughService implements ReachThroughServiceInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityFieldManager;

  public function __construct(EntityFieldManagerInterface $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * @inheritdoc
   */
  public function geReachThroughFields($reach_through_bundle) {
    $mappable_fields = [];

    $fields = $this->entityFieldManager->getFieldDefinitions('reach_through', $reach_through_bundle);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
    foreach ($fields as $field) {
      if ($field instanceof FieldConfigInterface) {
        $mappable_fields[$field->getName()] = $field->label();
      }
    }

    return $mappable_fields;
  }

  /**
   * @inheritdoc
   */
  public function alterNodeForm (&$form, FormStateInterface $form_state) {
    $all_reach_through_types = ReachThroughType::loadMultiple();

    foreach ($all_reach_through_types as $reach_through_type) {
      $this->alterNodeFormForType($form, $form_state, $reach_through_type);
    }
  }

  public function getReachThoughtFieldMappings (EntityInterface $entity) {
    $bundle = $entity->bundle();
    $reach_through_details_for_bundle = [];

    /** @var \Drupal\node\NodeInterface $wrapped_entity */
    $wrapped_entity = $entity->reach_through_ref->entity;

    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load($wrapped_entity->bundle());

    $reach_through_details = $this->make_associative_array($node_type->getThirdPartySetting('ef_reach_through_content', 'reach_through_details', []));

    if (isset($reach_through_details[$bundle]['mapped_fields'])) {
      $reach_through_details_for_bundle = $this->make_associative_array($reach_through_details[$bundle]['mapped_fields']);
    }

    return $reach_through_details_for_bundle;
  }

  /**
   * @inheritdoc
   */
  public function viewReachThroughEntity (array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    $bundle = $entity->bundle();

    $reach_through_fields = $this->geReachThroughFields($bundle);

    /** @var \Drupal\node\NodeInterface $wrapped_entity */
    $wrapped_entity = $entity->reach_through_ref->entity;

    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load($wrapped_entity->bundle());

    $current_reach_through_details = $this->make_associative_array($node_type->getThirdPartySetting('ef_reach_through_content', 'reach_through_details', []));

    if (isset($current_reach_through_details[$bundle]['mapped_fields'])) {
      $reach_through_details_for_bundle = $this->make_associative_array($current_reach_through_details[$bundle]['mapped_fields']);

      foreach ($reach_through_fields as $reach_through_field_id => $field_label) {
        /** @var FieldConfigInterface $field_definition */
        $field_definition = $entity->getFieldDefinition($reach_through_field_id);

        if (in_array($field_definition->getType(), ['string']) && !isset($build[$reach_through_field_id][0]['#context']['value'])) {
          if (isset($reach_through_details_for_bundle[$reach_through_field_id]) && $reach_through_details_for_bundle[$reach_through_field_id] != 'not_mapped') {
            $field_on_node = $reach_through_details_for_bundle[$reach_through_field_id];
            $value_on_node = $wrapped_entity->{$field_on_node}->value;
            $entity->{$reach_through_field_id}->value = $value_on_node;
            $render_array = $entity->{$reach_through_field_id}->view($view_mode);
            $build[$reach_through_field_id] = $render_array;
          }
        }
      }
    }

  }
  /**
   * @inheritdoc
   */
  protected function alterNodeFormForType (&$form, FormStateInterface $form_state, ReachThroughType $reach_through_type) {
    $reach_through_type_id = $reach_through_type->id();
    $mappable_fields = $this->geReachThroughFields($reach_through_type_id);

    /** @var \Drupal\node\NodeTypeInterface $type */
    $type = $form_state->getFormObject()->getEntity();

    $node_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $type->id());

    $node_fields = array_filter($node_fields, function (FieldDefinitionInterface $node_field_definition) {
      return $node_field_definition->isDisplayConfigurable('form');
    });

    $options = ['not_mapped' => t('-- Not mapped --')];

    /**
     * @var  \Drupal\Core\Field\FieldDefinitionInterface $field_details
     */
    foreach ($node_fields as $field_id => $field_details) {
      $options[$field_id] = $field_details->getLabel();
    }

    asort($options);

    $form[$reach_through_type_id] = [
      '#type' => 'details',
      '#title' => $reach_through_type->label(),
      '#group' => 'additional_settings',
    ];

    $current_reach_through_details = $this->make_associative_array($type->getThirdPartySetting('ef_reach_through_content', 'reach_through_details', []));

    $current_values = isset($current_reach_through_details[$reach_through_type_id]['mapped_fields']) ? $this->make_associative_array($current_reach_through_details[$reach_through_type_id]['mapped_fields']) : [];

    $form[$reach_through_type_id][$reach_through_type_id . '_field_mapping'] = [
      '#tree' => TRUE,
    ];

    foreach ($mappable_fields as $field_id => $field_name) {
      $form[$reach_through_type_id][$reach_through_type_id . '_field_mapping'][$field_id] = [
        '#type' => 'select',
        '#title' => t('Field for @field_name', ['@field_name' => strtolower($field_name)]),
        '#default_value' => isset($current_values[$field_id]) ? $current_values[$field_id] : 'not_mapped',
        '#options' => $options,
        '#description' => t('Field to use for the @type @field_name field.', ['@type' => strtolower($reach_through_type->label()), '@field_name' => strtolower($field_name)]),
      ];
    }

    $form['#entity_builders'][] = [ReachThroughService::class, 'reach_through_node_type_form_builder_callback'];
  }

  /**
   * Entity builder for the node type form with curated content options.
   *
   * @see ef_curated_content_form_node_type_form_alter()
   */
  public static function reach_through_node_type_form_builder_callback($entity_type, NodeTypeInterface $type, &$form, FormStateInterface $form_state) {
    $all_reach_through_types = ReachThroughType::loadMultiple();

    $reach_through_details = [];

    /** @var ReachThroughType $reach_through_type */
    foreach ($all_reach_through_types as $reach_through_type) {
      $reach_through_type_id = $reach_through_type->id();
      $transformed_field_mappings = [];
      $field_mappings = $form_state->getValue($reach_through_type_id . '_field_mapping');

      foreach ($field_mappings as $curated_content_field_name => $node_field_name) {
        $transformed_field_mappings[] = [
          'reach_through_bundle_field' => $curated_content_field_name,
          'node_field' => $node_field_name,
        ];
      }

      $reach_through_details[] = [
        'reach_through_bundle_id' => $reach_through_type_id,
        'settings' => [
          'mapped_fields' => $transformed_field_mappings,
        ],
      ];
    }

    $type->setThirdPartySetting('ef_reach_through_content', 'reach_through_details', $reach_through_details);
  }

  protected function make_associative_array ($settings) {
    $result = [];

    foreach ($settings as $setting) {
      $key = reset($setting);
      $value = next($setting);
      $result[$key] = $value;
    }

    return $result;
  }

}