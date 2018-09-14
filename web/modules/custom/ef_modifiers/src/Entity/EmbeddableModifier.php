<?php

namespace Drupal\ef_modifiers\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\ef_modifiers\EmbeddableModifierInterface;

/**
 * Defines the embeddable modifier configuration entity.
 *
 * @ConfigEntityType(
 *   id = "embeddable_modifier",
 *   label = @Translation("Embeddable modifier"),
 *   handlers = {
 *     "list_builder" = "Drupal\ef_modifiers\EmbeddableModifierListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ef_modifiers\Form\EmbeddableModifierAddForm",
 *       "edit" = "Drupal\ef_modifiers\Form\EmbeddableModifierEditForm",
 *       "delete" = "Drupal\ef_modifiers\Form\EmbeddableModifierDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer embeddable content",
 *   config_prefix = "modifier",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embeddable-modifier/{embeddable_modifier}",
 *     "delete-form" = "/admin/config/content/embeddable-modifier/{embeddable_modifier}/delete",
 *     "add-option" = "/admin/config/content/embeddable-modifier/manage/{embeddable_modifier}/add-option",
 *     "collection" = "/admin/config/content/embeddable-modifier"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "class_name",
 *     "editorial_name",
 *     "description",
 *     "tooltip",
 *     "promote",
 *     "default_option"
 *   }
 * )
 */
class EmbeddableModifier extends ConfigEntityBase implements EmbeddableModifierInterface   {
  /** @var string The name displayed to an editor */
  protected $editorial_name = '';

  /** @var string The base CSS class name for the modifier */
  protected $class_name = '';

  /** @var string A potential description */
  protected $description = '';

  /** @var string A potential tooltip */
  protected $tooltip = '';

  /** @var bool Indicates whether the modifier should be applied on the container, rather than the embeddable */
  protected $promote = FALSE;

  /** @var string The default option */
  protected $default_option = '';

  /** @var int The weight */
  protected $weight = 0;

  /**
   * @inheritdoc
   */
  public function getAdministrativeName() {
    return $this->label();
  }

  /**
   * @inheritdoc
   */
  public function getClassName() {
    return $this->class_name;
  }

  /**
   * @inheritdoc
   */
  public function getEditorialName() {
    return $this->editorial_name;
  }

  /**
   * @inheritdoc
   */
  public function getEditorialDisplayName() {
    return isset($this->editorial_name) && strlen($this->editorial_name) > 0 ? $this->editorial_name : $this->getAdministrativeName();
  }

  /**
   * @inheritdoc
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @inheritdoc
   */
  public function getTooltip() {
    return $this->tooltip;
  }

  /**
   * @inheritdoc
   */
  public function isPromoted() {
    return $this->promote;
  }

  /**
   * @inheritdoc
   */
  public function getDefaultOption() {
    return $this->default_option;
  }

  /**
   * @inheritdoc
   */
  public function getDefaultOptionObject() {
    $defaultOption = $this->getDefaultOption();
    return isset($defaultOption) ? EmbeddableModifierOption::load($defaultOption) : NULL;
  }

  public function getWeight() {
    return $this->weight;
  }

  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    foreach ($entities as $entity) {
      // Delete the options
      $option_ids = \Drupal::entityQuery('embeddable_modifier_option')
        ->condition('target_embeddable_modifier', $entity->id(), '=')
        ->execute();

      $controller = \Drupal::entityTypeManager()->getStorage('embeddable_modifier_option');
      $entities = $controller->loadMultiple($option_ids);
      $controller->delete($entities);
    }
  }

  /**
   * @return array of all EmbeddableModifiers, ordered by their weight
   */
  public static function getAllModifierList ($labelOnly = TRUE) {
    return self::getModifierList(NULL, $labelOnly);
  }

  public static function getModifierList ($ids = NULL, $labelOnly = TRUE) {
    $modifiersFull = static::loadMultiple($ids);

    uasort($modifiersFull, ['\Drupal\ef_modifiers\Entity\EmbeddableModifier', 'sort']);

    $modifiers = [];

    /** @var EmbeddableModifier $modifier */
    foreach ($modifiersFull as $modifier) {
      $modifiers[$modifier->id()] = $labelOnly ? $modifier->label() : $modifier;
    }

    return $modifiers;
  }

  /**
   * Returns all the options from a embeddable modifier set sorted correctly.
   *
   * @return \Drupal\ef_modifiers\EmbeddableModifierOptionInterface[]
   *   An array of embeddable modifier entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getOptions() {
    $options = \Drupal::entityTypeManager()->getStorage('embeddable_modifier_option')->loadByProperties(['target_embeddable_modifier' => $this->id()]);
    uasort($options, ['\Drupal\ef_modifiers\Entity\EmbeddableModifierOption', 'sort']);
    return $options;
  }
}