<?php

namespace Drupal\ef;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Entity\EmbeddableType;

/**
 * Class DependentEmbeddableService
 * @package Drupal\ef
 */
class DependentEmbeddableService implements DependentEmbeddableServiceInterface {
  use EmbeddableReferencesTrait;
  use StringTranslationTrait;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface  */
  protected $moduleHandler;

  public function __construct(TranslationInterface $translation, ModuleHandlerInterface $moduleHandler) {
    $this->setStringTranslation($translation);
    $this->moduleHandler = $moduleHandler;
  }

  public function isDependentEmbeddableType ($embeddable_type) {
    $embeddableType = EmbeddableType::load($embeddable_type);

    return $embeddableType->isDependentType();
  }

  /**
   * @inheritdoc
   */
  public function onPresave(ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
    $embeddable_reference_fields = $this->getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity($entity);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface $embeddable_reference_field */
    foreach ($embeddable_reference_fields as $embeddable_reference_field) {
      $field_machine_name = $embeddable_reference_field->getName();


      // we have two pathways based on whether we are the default translation
      // or whether we are an added translation
      if ($entity->isDefaultTranslation()) {
        // check that we did not already create a dependent embeddable for this
        // field
        if (!$entity->{$field_machine_name}->target_id) {
          // create the dependent embeddable from scratch
          $this->createDependentEmbeddable($entity, $embeddable_reference_field);
        }
      } else {
        /** @var ContentEntityInterface $untranslated_entity */
        $untranslated_entity = $entity->getUntranslated();
        $field_machine_name = $embeddable_reference_field->getName();
        $dependent_embeddable_id = $untranslated_entity->{$field_machine_name}->target_id;
        $untranslated_embeddable = Embeddable::load($dependent_embeddable_id);

        $entity_language_code = $entity->language()->getId();
        // check that we did not already create the translation
        if (!$untranslated_embeddable->hasTranslation($entity_language_code)) {
          // add translation to the existing dependent embeddable
          $untranslated_embeddable->addTranslation($entity_language_code, ['title' => $this->generateDependentEmbeddableTitle($untranslated_embeddable->bundle(), $entity)]);
          $untranslated_embeddable->save();
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function onInsert(ContentEntityInterface $parent_entity) {
    if ($parent_entity->isDefaultTranslation()) {
      /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
      $embeddable_reference_fields = $this->getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity($parent_entity);

      foreach ($embeddable_reference_fields as $embeddable_reference_field) {
        $field_machine_name = $embeddable_reference_field->getName();
        $dependent_embeddable_id = $parent_entity->{$field_machine_name}->target_id;

        /** @var \Drupal\ef\EmbeddableInterface $dependent_embeddable */
        $dependent_embeddable = Embeddable::load($dependent_embeddable_id);
        $dependent_embeddable->setParent($parent_entity);
        $dependent_embeddable->save();
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function onUpdate(ContentEntityInterface $parent_entity) {

    /** @var ContentEntityInterface $original_entity */
    $original_version_of_parent_entity = $parent_entity->original;

    if ($original_version_of_parent_entity->language()->getId() == $parent_entity->language()->getId()) {
      $original_title = $original_version_of_parent_entity->label();
      $current_title = $parent_entity->label();

      if (strcmp($original_title, $current_title) !== 0) {
        /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
        $embeddable_reference_fields = $this->getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity($parent_entity);

        foreach ($embeddable_reference_fields as $embeddable_reference_field) {
          $field_machine_name = $embeddable_reference_field->getName();
          $dependent_embeddable_id = $parent_entity->{$field_machine_name}->target_id;

          /** @var \Drupal\ef\EmbeddableInterface $dependent_embeddable */
          $dependent_embeddable = Embeddable::load($dependent_embeddable_id);

          if (!$parent_entity->isDefaultTranslation()) {
            $parent_language_id = $parent_entity->language()->getId();
            $dependent_embeddable->getTranslation($parent_language_id);
          }

          $embeddable_bundle_name = $dependent_embeddable->bundle();
          $dependent_embeddable->setTitle($this->generateDependentEmbeddableTitle ($embeddable_bundle_name, $parent_entity));

          $this->moduleHandler->alter('dependent_embeddable_presave_' . $embeddable_bundle_name, $dependent_embeddable, $parent_entity);
          $this->moduleHandler->alter('dependent_embeddable_presave', $dependent_embeddable, $parent_entity);

          $dependent_embeddable->save();
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function onDelete(ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
    $embeddable_reference_fields = $this->getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity($entity);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface $embeddable_reference_field */
    foreach ($embeddable_reference_fields as $embeddable_reference_field) {
      $field_machine_name = $embeddable_reference_field->getName();
      $dependent_embeddable_id = $entity->{$field_machine_name}->target_id;
      $dependent_embeddable = Embeddable::load($dependent_embeddable_id);

      if ($dependent_embeddable) {
        $entity_language = $entity->language()->getId();

        if ($dependent_embeddable->hasTranslation($entity_language)) {
          $dependent_embeddable_translated_version = $dependent_embeddable->getTranslation($entity_language);

          if (!$dependent_embeddable_translated_version->isDefaultTranslation()) {
            $dependent_embeddable->removeTranslation($entity_language);
            $dependent_embeddable->save();
          } else {
            $dependent_embeddable->delete();
          }
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function onTranslationDelete(ContentEntityInterface $entity) {
    $this->onDelete($entity);
  }

  protected function generateDependentEmbeddable ($embeddable_bundle_name, ContentEntityInterface $parent_entity) {

    $language_code = $parent_entity->language()->getId();

    $dependent_embeddable = Embeddable::create([
      'type' => $embeddable_bundle_name,
      'langcode' => $language_code,
      'title' => $this->generateDependentEmbeddableTitle($embeddable_bundle_name, $parent_entity),
    ]);

    $translation_languages = $parent_entity->getTranslationLanguages();

    unset($translation_languages[$language_code]);

    foreach ($translation_languages as $language_code => $language) {
      $parent_entity = $parent_entity->getTranslation($language_code);
      $parent_entity->addTranslation($language_code, [
        'title' => $this->generateDependentEmbeddableTitle($embeddable_bundle_name, $parent_entity),
      ]);
    }

    $this->moduleHandler->alter('dependent_embeddable_presave_' . $embeddable_bundle_name, $dependent_embeddable, $parent_entity);
    $this->moduleHandler->alter('dependent_embeddable_presave', $dependent_embeddable, $parent_entity);

    return $dependent_embeddable;
  }

  protected function generateDependentEmbeddableTitle ($embeddable_bundle_name, ContentEntityInterface $parent_entity) {
    $embeddable_bundle = EmbeddableType::load($embeddable_bundle_name);

    return $this->t('@bundle_type for @parent_label', ['@bundle_type' => $embeddable_bundle->label(), '@parent_label' => $parent_entity->label()]);
  }

  protected function createDependentEmbeddable(ContentEntityInterface $parent_entity, FieldDefinitionInterface $dependent_embeddable_field_on_parent) {
    $handler_settings = $dependent_embeddable_field_on_parent->getSetting('handler_settings');
    $target_bundle_array = $handler_settings['target_bundles'];
    $target_bundle = key($target_bundle_array);

    $dependent_embeddable = $this->generateDependentEmbeddable($target_bundle, $parent_entity);

    $dependent_embeddable->save();
    $dependent_embeddable_id = $dependent_embeddable->id();
    $field_machine_name = $dependent_embeddable_field_on_parent->getName();
    $parent_entity->{$field_machine_name} = [
      'target_id' => $dependent_embeddable_id,
      'mode' => EmbeddableReferenceModeInterface::ENABLED,
    ];
  }
}
