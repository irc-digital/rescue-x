<?php

namespace Drupal\ef\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Exception\DeleteInUseEmbeddableException;
use Drupal\user\UserInterface;

/**
 * Defines the embeddable entity class.
 *
 * @ContentEntityType(
 *   id = "embeddable",
 *   label = @Translation("Embeddable"),
 *   bundle_label = @Translation("Embeddable type"),
 *   label_collection = @Translation("Embeddables"),
 *   handlers = {
 *     "views_data" = "Drupal\ef\EmbeddableViewsData",
 *     "view_builder" = "Drupal\ef\EmbeddableViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\ef\Form\EmbeddableForm",
 *       "edit" = "Drupal\ef\Form\EmbeddableForm",
 *       "delete" = "Drupal\ef\Form\EmbeddableDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "list_builder" = "Drupal\ef\EmbeddableListBuilder",
 *     "access" = "Drupal\ef\Access\EmbeddableAccessControlHandler",
 *   },
 *   base_table = "embeddable",
 *   data_table = "embeddable_field_data",
 *   revision_table = "embeddable_revision",
 *   revision_data_table = "embeddable_revision_field_data",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log"
 *   },
 *   admin_permission = "administer embeddable content",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "revision" = "vid",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "embeddable_type",
 *   links = {
 *     "canonical" = "/embeddable/{embeddable}",
 *     "edit-form" = "/embeddable/{embeddable}/edit",
 *     "delete-form" = "/embeddable/{embeddable}/delete",
 *     "revision" = "/embeddable/{embeddable}/revisions/{embeddable_revision}/view",
 *   },
 *   fieldable = TRUE,
 *   field_ui_base_route = "entity.embeddable_type.edit_form",
 *   translatable = TRUE,
 *   permission_granularity = "bundle"
 * )
 */
class Embeddable extends ContentEntityBase implements EmbeddableInterface {

  use EntityChangedTrait;
  use RevisionLogEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the node owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing node without adding a new revision, we
      // need to make sure $entity->revision_log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    /** @var EmbeddableInterface $embeddable */
    foreach ($entities as $embeddable) {
      self::preventDeletionIfInUse ($embeddable);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeTranslation($langcode) {
    self::preventDeletionIfInUse ($this, $this->getParentEntity());
    parent::removeTranslation($langcode);
  }

  protected static function preventDeletionIfInUse (EmbeddableInterface $embeddable, ContentEntityInterface $exclude = NULL) {
    /** @var \Drupal\ef\EmbeddableUsageServiceInterface $embeddableUsageService */
    $embeddableUsageService = \Drupal::service('ef.embeddable_usage');
    if ($embeddableUsageService->isInUse($embeddable, $exclude)) {
      throw new DeleteInUseEmbeddableException($embeddable);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentType() {
    $result = NULL;

    $parent_type = $this->get('parent_type');

    if ($parent_type) {
      $result = $parent_type->value;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentId() {
    $result = NULL;

    $parent_id = $this->get('parent_id');

    if ($parent_id) {
      $result = $parent_id->value;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    $result = NULL;

    $parent_type = $this->get('parent_type');
    $parent_id = $this->get('parent_id');

    if (isset($parent_type->value) && isset($parent_id->value)) {
      /** @var EntityStorageInterface $storage */
      $storage = $this->entityTypeManager()->getStorage($parent_type->value);
      $result = $storage->load($parent_id->value);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function setParent(ContentEntityInterface $contentEntity) {
    $this->set('parent_id', $contentEntity->id());
    $this->set('parent_type', $contentEntity->getEntityTypeId());

    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the revision metadata fields.
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The internal title of the embeddable entity. This will be used wherever this is presented to an editor.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the embeddable author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\ef\Entity\Embeddable::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created on'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDescription(t('The time that the embeddable was last edited.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['parent_type'] = BaseFieldDefinition::create('string')
      ->setRequired(FALSE)
      ->setLabel(t('Parent type'))
      ->setTranslatable(FALSE)
      ->setRevisionable(FALSE)
      ->setDescription(t('The type of the parent entity when this embeddable is a dependent embeddable.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    $fields['parent_id'] = BaseFieldDefinition::create('embeddable_parent_id')
      ->setRequired(FALSE)
      ->setLabel(t('Parent ID'))
      ->setTranslatable(FALSE)
      ->setRevisionable(FALSE)
      ->setDescription(t('The ID of the parent entity when this embeddable is a dependent embeddable.'))
      ->setSetting('is_ascii', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see Embeddable::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }
}
