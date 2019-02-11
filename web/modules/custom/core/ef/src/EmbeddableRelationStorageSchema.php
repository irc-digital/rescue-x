<?php


namespace Drupal\ef;


use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

class EmbeddableRelationStorageSchema extends SqlContentEntityStorageSchema {
  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema['embeddable_relation']['indexes'] += [
      'embeddable_relation__embeddable_id' => ['embeddable_id'],
      'embeddable_relation__referer' => ['referring_id', 'referring_type', 'referring_field_name'],
    ];

    return $schema;
  }
}