<?php

namespace Drupal\ef_modifiers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ef_modifiers\EmbeddableModifierInterface;
use Drupal\ef_modifiers\EmbeddableModifierSetInterface;

/**
 * A controller
 */
class EmbeddableModifierController extends ControllerBase {

  /**
   * Returns a form to add a new embeddable modifier to a given set.
   *
   * @param \Drupal\ef_modifiers\EmbeddableModifierInterface $embeddable_modifier
   *   The embeddable modifier set this modifier will be added to.
   *
   * @return array
   *   The embeddable modifier entry add form.
   */
  public function addOptionForm(EmbeddableModifierInterface $embeddable_modifier) {
    $embeddable_modifier = $this->entityTypeManager()->getStorage('embeddable_modifier_option')->create(['target_embeddable_modifier' => $embeddable_modifier->id()]);
    return $this->entityFormBuilder()->getForm($embeddable_modifier, 'add');
  }

  /**
   * The _title_callback for the entity.embeddable_modifier_set.customize_form route.
   *
   * @param \Drupal\ef_modifiers\EmbeddableModifierSetInterface $embeddable_modifier_set
   *   The type of embeddable modifier type.
   *
   * @return string
   *   The page title.
   */
  public function setListPageTitle(EmbeddableModifierSetInterface $embeddable_modifier_set) {
    return $this->t('List modifier options for the @name set', ['@name' => $embeddable_modifier_set->label()]);
  }

}
