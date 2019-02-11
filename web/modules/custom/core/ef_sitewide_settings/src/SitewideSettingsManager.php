<?php


namespace Drupal\ef_sitewide_settings;
use Drupal\Core\Entity\EntityTypeManagerInterface;


/**
 * Class SitewideSettingsManager
 *
 * @package Drupal\ef_sitewide_settings
 */
class SitewideSettingsManager implements SitewideSettingsManagerInterface {

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @inheritdoc
   */
  public function getSitewideSettingsForType ($sitewide_settings_type_id) {
    $storage = $this->entityTypeManager->getStorage('sitewide_settings');

    $ids = $storage->getQuery()->condition('type', $sitewide_settings_type_id, '=')->execute();

    if (sizeof($ids) > 0) {
      return $storage->load(key($ids));
    } else {
      return NULL;
    }
  }
}