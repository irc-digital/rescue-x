<?php

namespace Drupal\ef\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ef\EmbeddableTypeInterface;

/**
 * Defines the Embeddable type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "embeddable_type",
 *   label = @Translation("Embeddable type"),
 *   handlers = {
 *     "access" = "Drupal\ef\Access\EmbeddableTypeAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\ef\Form\EmbeddableTypeForm",
 *       "add" = "Drupal\ef\Form\EmbeddableTypeForm",
 *       "edit" = "Drupal\ef\Form\EmbeddableTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "list_builder" = "Drupal\ef\EmbeddableTypeListBuilder",
 *   },
 *   admin_permission = "administer embeddable content",
 *   config_prefix = "type",
 *   bundle_of = "embeddable",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/advertiser_type/{advertiser_type}",
 *     "edit-form" = "/admin/structure/embeddable_type/{embeddable_type}/edit",
 *     "add-form" = "/admin/structure/embeddable_type/add",
 *     "delete-form" = "/admin/structure/embeddable_type/{embeddable_type}/delete",
 *     "collection" = "/admin/structure/embeddable_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "exclude_from_embeddable_overview_quick_add_list",
 *     "dependent_embeddable",
 *     "only_dependent_embeddable"
 *   }
 * )
 */
class EmbeddableType extends ConfigEntityBundleBase implements EmbeddableTypeInterface {

  /**
   * A brief description of this node type.
   *
   * @var string
   */
  protected $description;

  /**
   * A flag indicating whether this type should be excluded from the embeddable
   * overview screen 'quick add' buttons
   *
   * @var boolean
   */
  protected $exclude_from_embeddable_overview_quick_add_list = FALSE;

  /**
   * If true an editor may not create embeddables of this type directly
   *
   * @var bool
   */
  protected $dependent_embeddable = FALSE;

  /**
   * If the embeddable type marked as supporting being a dependent embeddable?
   *
   * @var bool
   */
  protected $only_dependent_embeddable = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function isExcludedFromEmbeddableOverviewQuickAddList() {
    return $this->exclude_from_embeddable_overview_quick_add_list;
  }

  public function isDependentType () {
    return $this->dependent_embeddable;
//    if (is_null($this->id())) {
//      return FALSE;
//    }
//    /** @var \Drupal\ef\DependentEmbeddableServiceInterface $dependentEmbeddableService */
//    $dependentEmbeddableService = \Drupal::service('ef.dependent_embeddable');
//    return $dependentEmbeddableService->isDependentEmbeddableType($this->id());
  }

  public function isOnlyDependentType () {
    return $this->isDependentType() && $this->only_dependent_embeddable;
  }
}
