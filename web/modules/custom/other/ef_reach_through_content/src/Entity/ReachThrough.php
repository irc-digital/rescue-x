<?php

namespace Drupal\ef_reach_through_content\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Reach-through entry entity.
 *
 * @ingroup ef_reach_through_content
 *
 * @ContentEntityType(
 *   id = "reach_through",
 *   label = @Translation("Reach-through entry"),
 *   bundle_label = @Translation("Reach-through entry type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ef_reach_through_content\ReachThroughListBuilder",
 *     "views_data" = "Drupal\ef_reach_through_content\Entity\ReachThroughViewsData",
 *     "translation" = "Drupal\ef_reach_through_content\ReachThroughTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\ef_reach_through_content\Form\ReachThroughForm",
 *       "add" = "Drupal\ef_reach_through_content\Form\ReachThroughForm",
 *       "edit" = "Drupal\ef_reach_through_content\Form\ReachThroughForm",
 *       "delete" = "Drupal\ef_reach_through_content\Form\ReachThroughDeleteForm",
 *     },
 *     "access" = "Drupal\ef_reach_through_content\ReachThroughAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ef_reach_through_content\ReachThroughHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "reach_through",
 *   data_table = "reach_through_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer reach-through entry entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/reach_through/{reach_through}",
 *     "add-page" = "/admin/structure/reach_through/add",
 *     "add-form" = "/admin/structure/reach_through/add/{reach_through_type}",
 *     "edit-form" = "/admin/structure/reach_through/{reach_through}/edit",
 *     "delete-form" = "/admin/structure/reach_through/{reach_through}/delete",
 *     "collection" = "/admin/structure/reach_through",
 *   },
 *   bundle_entity_type = "reach_through_type",
 *   field_ui_base_route = "entity.reach_through_type.edit_form"
 * )
 */
class ReachThrough extends ContentEntityBase implements ReachThroughInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Reach-through entry entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Reach-through entry entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['reach_through_ref'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Reach-through entity reference'))
      ->setDescription(t('The reference to the node this is reaching through to.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}
