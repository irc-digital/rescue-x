<?php

namespace Drupal\ef_social_share\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SocialShareSite annotation object.
 *
 * Plugin Namespace: Plugin\Action
 *
 *
 * @Annotation
 */
class SocialShareSite extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the action plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
