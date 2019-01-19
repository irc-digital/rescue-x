<?php

namespace Drupal\ef_footer_2\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for the lower footer.
 *
 * @Block(
 *   id = "footer_2_block",
 *   admin_label = @Translation("Lower footer"),
 *   category = @Translation("Custom"),
 * )
 */
class Footer2Block extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ef_footer_2',
    ];
  }
}