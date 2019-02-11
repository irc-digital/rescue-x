<?php

namespace Drupal\ef_footer_1\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for the lower footer.
 *
 * @Block(
 *   id = "footer_1_block",
 *   admin_label = @Translation("Upper footer"),
 *   category = @Translation("Custom"),
 * )
 */
class Footer1Block extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ef_footer_1',
    ];
  }
}