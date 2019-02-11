<?php

namespace Drupal\ef_reach_through_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Reach-through entry type entity.
 *
 * @ConfigEntityType(
 *   id = "reach_through_type",
 *   label = @Translation("Reach-through entry type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ef_reach_through_content\ReachThroughTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ef_reach_through_content\Form\ReachThroughTypeForm",
 *       "edit" = "Drupal\ef_reach_through_content\Form\ReachThroughTypeForm",
 *       "delete" = "Drupal\ef_reach_through_content\Form\ReachThroughTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ef_reach_through_content\ReachThroughTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "reach_through_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "reach_through",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/reach_through_type/{reach_through_type}",
 *     "add-form" = "/admin/structure/reach_through_type/add",
 *     "edit-form" = "/admin/structure/reach_through_type/{reach_through_type}/edit",
 *     "delete-form" = "/admin/structure/reach_through_type/{reach_through_type}/delete",
 *     "collection" = "/admin/structure/reach_through_type"
 *   }
 * )
 */
class ReachThroughType extends ConfigEntityBundleBase implements ReachThroughTypeInterface {

  /**
   * The Reach-through entry type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Reach-through entry type label.
   *
   * @var string
   */
  protected $label;

}
