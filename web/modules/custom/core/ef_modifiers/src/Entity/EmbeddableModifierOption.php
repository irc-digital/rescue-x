<?php

namespace Drupal\ef_modifiers\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ef_modifiers\EmbeddableModifierInterface;
use Drupal\ef_modifiers\EmbeddableModifierOptionInterface;

/**
 * Defines the embeddable modifier option configuration entity.
 *
 * @ConfigEntityType(
 *   id = "embeddable_modifier_option",
 *   label = @Translation("Embeddable modifier option"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\ef_modifiers\Form\EmbeddableModifierOptionAddForm",
 *       "edit" = "Drupal\ef_modifiers\Form\EmbeddableModifierOptionEditForm",
 *       "delete" = "Drupal\ef_modifiers\Form\EmbeddableModifierOptionDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer embeddable content",
 *   config_prefix = "option",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embeddable-modifier/option/{embeddable_modifier_option}/edit",
 *     "delete-form" = "/admin/config/content/embeddable-modifier/option/{embeddable_modifier_option}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "class_name",
 *     "target_embeddable_modifier",
 *     "weight"
 *   }
 * )
 */
class EmbeddableModifierOption extends ConfigEntityBase implements EmbeddableModifierOptionInterface {

  /**
   * The class name
   * @var string
   */
  protected $class_name;

  /**
   * The embeddable modifier this option is associated with
   *
   * @var string
   */
  protected $target_embeddable_modifier;

  /**
   * The weight of the option within the modifier set
   *
   * @var integer
   */
  protected $weight;

  /**
   * @inheritdoc
   */
  public function getClassName() {
    return $this->class_name;
  }

  /**
   * @inheritdoc
   */
  public function getFullClassName() {
    $embeddableModifier = EmbeddableModifier::load($this->target_embeddable_modifier);

    return sprintf ('%s-%s', $embeddableModifier->getClassName(), $this->getClassName());
  }

  /**
   * @inheritdoc
   */
  public function getTargetEmbeddableModifier() {
    return $this->target_embeddable_modifier;
  }

  /**
   * @inheritdoc
   */
  public function setTargetEmbeddableModifier($target_modifier) {
    $this->target_embeddable_modifier = $target_modifier;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * @inheritdoc
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  public function __toString() {
    return $this->label();
  }
}