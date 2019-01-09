<?php

namespace Drupal\ef_sitewide_settings\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ef_sitewide_settings\SitewideSettingsTypeInterface;

/**
 * Defines the Crop type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "sitewide_settings_type",
 *   label = @Translation("Sitewide settings type"),
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\ef_sitewide_settings\Form\SitewideSettingsTypeEntityForm",
 *       "add" = "Drupal\ef_sitewide_settings\Form\SitewideSettingsTypeEntityForm",
 *       "edit" = "Drupal\ef_sitewide_settings\Form\SitewideSettingsTypeEntityForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *    "list_builder" = "Drupal\ef_sitewide_settings\SitewideSettingsTypeListBuilder",
 *   },
 *   admin_permission = "administer sitewide settings content",
 *   config_prefix = "type",
 *   bundle_of = "sitewide_settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/sitewide_settings_type/{sitewide_settings_type}",
 *     "add-form" = "/admin/structure/sitewide_settings_type/add",
 *     "edit-form" = "/admin/structure/sitewide_settings_type/{sitewide_settings_type}/edit",
 *     "delete-form" = "/admin/structure/sitewide_settings_type/{sitewide_settings_type}/delete",
 *     "collection" = "/admin/structure/sitewide_settings_type",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   }
 * )
 */
class SitewideSettingsType extends ConfigEntityBundleBase implements SitewideSettingsTypeInterface {
  /**
   * A brief description of this node type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }
}

