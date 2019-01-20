<?php


namespace Drupal\ef_sitewide_settings\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\ef_sitewide_settings\Exception\DuplicateSettingNotPermittedException;
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
 *       "default" = "Drupal\ef_sitewide_settings\Form\SitewideSettingsForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "edit" = "Drupal\ef_sitewide_settings\Form\SitewideSettingsForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "list_builder" = "Drupal\ef_sitewide_settings\SitewideSettingsListBuilder",
 *     "access" = "Drupal\ef_sitewide_settings\Access\SitewideSettingsAccessControlHandler",
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
 *   links = {
 *     "canonical" = "/sitewide-settings/{sitewide_settings}",
 *     "edit-form" = "/sitewide-settings/{sitewide_settings}/edit",
 *     "delete-form" = "/sitewide-settings/{sitewide_settings}/delete",
 *   },
 *   permission_granularity = "bundle",
 *   admin_permission = "administer sitewide settings",
 *   field_ui_base_route = "entity.sitewide_settings_type.edit_form",
 * )
 */
class SitewideSettings extends ContentEntityBase implements SitewideSettingsInterface {
  public function label() {
    return $this->type->entity->label();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // check that we do not already have setting for the type

    $type = $this->bundle();

    $existing_setting = \Drupal::service('ef_sitewide_settings.manager')->getSitewideSettingsForType($type);

    if (!is_null($existing_setting) && $existing_setting->id() != $this->id()) {
      throw new DuplicateSettingNotPermittedException($this);
    }
  }
}