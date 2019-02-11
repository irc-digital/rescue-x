<?php

namespace Drupal\ef_social_share;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface for a SocialShareSite plugin.
 */
interface SocialShareSiteInterface extends PluginInspectionInterface {
  /** Returns the icon name */
  public function getIcon();

  /**
   * @param array $context
   * @return mixed
   */
  public function getLink (array $context = []);

  public function getLibraries (array $context = []);

  public function shouldOpenInPopup ();
}
