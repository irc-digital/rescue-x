<?php

namespace Drupal\ef_social_share;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a social share site entity.
 */
interface SocialShareSiteConfigEntityInterface extends ConfigEntityInterface {
  /**
   * Returns the operation plugin.
   *
   * @return \Drupal\ef_social_share\SocialShareSiteInterface
   */
  public function getPlugin();

}
