<?php

namespace Drupal\ef_wysiwyg_embed\Plugin\EmbeddableUsage;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\ef\EmbeddableUsageInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableUsage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WysiwygUsage
 * @package Drupal\ef_test\Plugin\EmbeddableUsage
 *
 * @EmbeddableUsage(
 *  id = "wysiwyg_usage"
 * )
 */
class WysiwygUsage extends PluginBase implements EmbeddableUsageInterface, ContainerFactoryPluginInterface {
  /** @var EntityFieldManagerInterface $entityFieldManager */
  protected $entityFieldManager;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @inheritdoc
   */
  public function getUsedEmbeddableEntities(EntityInterface $entity) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $result = [];

    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
    $rich_text_fields = $this->getAllRichTextFields ($entity_type_id, $bundle);

    foreach ($rich_text_fields as $rich_text_field) {
      $field_name = $rich_text_field->getName();

      /** @var \Drupal\Core\Field\FieldItemListInterface $rich_text_details */
      $rich_text_details = $entity->get($field_name);

      $all_embeddable_ids = [];
      /** @var \Drupal\Core\Field\FieldItemInterface $rich_text_details_item */
      foreach($rich_text_details as $rich_text_details_item) {
        $text_value = $rich_text_details_item->value;
        $embeddable_ids = $this->extractEmbeddableIdsFromText($text_value);

        $all_embeddable_ids += $embeddable_ids;
      }

      if (count($all_embeddable_ids) > 0) {
        $result[$field_name] = array_keys($all_embeddable_ids);
      }
    }

    return $result;
  }

  /**
   * @param string $text The text being scanned for embedded entities
   *
   * @return array of embeddable ids
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function extractEmbeddableIdsFromText (string $text) {
    $result = [];

    $embeddable_uuids = [];

    if (strpos($text, 'data-embed-button="embeddable"') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//drupal-entity[@data-embed-button="embeddable"]') as $node) {
        /** @var \DOMElement $node */
        $embeddable_uuids[] = $node->getAttribute('data-entity-uuid');
      }
    }

    if (count($embeddable_uuids) > 0) {
      $result = $this->entityTypeManager->getStorage('embeddable')->getQuery()
        ->condition('uuid', $embeddable_uuids, 'IN')
        ->execute();

      $result = array_combine($result, $result);
    }

    return $result;
  }

  /**
   * Get any definitions that correspond to our embeddable reference field type
   * @param string $entity_type_id
   * @param string $bundle
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  protected function getAllRichTextFields ($entity_type_id, $bundle) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $result = [];

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);

    foreach ($field_definitions as $field_definition) {
      if (in_array($field_definition->getType(), ['text_long', 'text_with_summary'])) {
        $result[] = $field_definition;
      }
    }

    return $result;
  }

}