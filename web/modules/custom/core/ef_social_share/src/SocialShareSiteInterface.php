<?php

namespace Drupal\ef_social_share;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface for a SocialShareSite plugin.
 */
interface SocialShareSiteInterface extends PluginInspectionInterface {
  public function renderSocialShareSite (array $content);
}
