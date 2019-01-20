<?php


namespace Drupal\ef\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ef\EmbeddableRelationInterface;
use Drupal\ef\Exception\DeleteInUseEmbeddableException;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Class EmbeddableRelation
 *
 * An entity that represents that stores the relationship between an embeddable
 * and the entities that it is embedded on.
 *
 * This is used to augment regular entity reference for embedding that does
 * not involve ERs - for example, this can be used when embedding in a WYSIWYG
 *
 * @ContentEntityType(
 *   id = "embeddable_relation",
 *   label = @Translation("Embeddable relation"),
 *   base_table = "embeddable_relation",
 *   handlers = {
 *     "storage_schema" = "Drupal\ef\EmbeddableRelationStorageSchema",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "embeddable_id" = "embeddable_id",
 *     "referring_id" = "referring_id",
 *     "referring_type" = "referring_type",
 *     "referring_field_name" = "referring_field_name",
 *   },
 *   fieldable = FALSE,
 *   translatable = FALSE,
 * )
 *
 * @package Drupal\ef\Entity
 */
class EmbeddableRelation extends ContentEntityBase implements EmbeddableRelationInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['embeddable_id'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Embeddable ID'))
      ->setDescription(t('The ID of the embeddable entity being referenced.'))
      ->setSetting('is_ascii', TRUE);

    $fields['referring_type'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Referring type'))
      ->setDescription(t('The referring entity type to which this embeddable is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    $fields['referring_id'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Referring ID'))
      ->setDescription(t('The ID of the referring entity of which this embeddable is referenced.'))
      ->setSetting('is_ascii', TRUE);

    $fields['referring_field_name'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Referring field name'))
      ->setDescription(t('The referring entity field name to which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', FieldStorageConfig::NAME_MAX_LENGTH);

    return $fields;
  }

  public function getEmbeddableId () {
    return $this->get('embeddable_id')->value;
  }

  public function getReferringEntityId () {
    return $this->get('referring_id')->value;
  }

  public function getReferringEntityType () {
    return $this->get('referring_type')->value;
  }

  public function getReferringEntityFieldName () {
    return $this->get('referring_field_name')->value;
  }

}