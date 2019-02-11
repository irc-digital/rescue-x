<?php

namespace Drupal\ef_social_share;

use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a container for lazily loading social share site plugins.
 */
class SocialShareSitePluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ef_social_share\SocialShareSiteInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

}
