<?php


namespace Drupal\ef_sitewide_settings;

/**
 * Interface SitewideSettingsManagerInterface
 *
 * Interface that defines the methods used to retrieve site-wide settings
 *
 * @package Drupal\ef_sitewide_settings
 */
interface SitewideSettingsManagerInterface {
  /**
   * @param $sitewide_settings_type
   * @return \Drupal\ef_sitewide_settings\Entity\SitewideSettings entity|null
   */
  public function getSitewideSettingsForType ($sitewide_settings_type);
}