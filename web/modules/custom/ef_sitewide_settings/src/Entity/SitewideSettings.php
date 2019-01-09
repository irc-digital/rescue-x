<?php


namespace Drupal\ef_sitewide_settings\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\ef_sitewide_settings\SitewideSettingsInterface;

/**
 * Defines the sitewide settings entity.
 *
 * @ContentEntityType(
 *   id = "sitewide_settings",
 *   label = @Translation("Sitewide settings"),
 *   bundle_label = @Translation("Sidewide settings type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityConfirmFormBase",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "sitewide_settings",
 *   data_table = "sitewide_settings_field_data",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "sitewide_settings_type",
 *   permission_granularity = "bundle",
 *   admin_permission = "administer sitewide settings content",
 *   links = {},
 *   field_ui_base_route = "entity.sitewide_settings_type.edit_form"
 * )
 */
class SitewideSettings extends ContentEntityBase implements SitewideSettingsInterface {

}