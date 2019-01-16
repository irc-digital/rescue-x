<?php

namespace Drupal\ef_global_navigation\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Global navigation' Block.
 *
 * @Block(
 *   id = "global_navigation_block",
 *   admin_label = @Translation("Global navigation"),
 *   category = @Translation("Custom"),
 * )
 */
class GlobalNavigationBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ef_global_navigation',
    ];
  }
}