<?php

namespace Drupal\ef_sitewide_settings;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ef_sitewide_settings\Entity\SitewideSettingsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for each different sitewide setting type.
 *
 * @see ef_sitewide_settings.permissions.yml
 */
class SitewideSettingsPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Get site-wide settings permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function sitewideSettingsTypePermissions() {
    $permissions = [];
    foreach (SitewideSettingsType::loadMultiple() as $type) {
      $permissions += $this->buildPermissions($type);
    }
    return $permissions;
  }

  /**
   * Builds a standard list of site-wide setting permissions for a given type.
   *
   * @param \Drupal\ef_sitewide_settings\SitewideSettingsTypeInterface $type
   *   The type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(SitewideSettingsTypeInterface $type) {
    $id = $type->id();
    $args = ['%type' => $type->label()];

    return [
      "edit $id sitewide settings" => ['title' => $this->t('%type: Edit site-wide settings', $args)],
    ];
  }

}
