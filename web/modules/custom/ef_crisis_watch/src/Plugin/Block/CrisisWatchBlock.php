<?php


namespace Drupal\ef_crisis_watch\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Crisis Watch block
 *
 * @Block(
 *   id = "crisis_watch",
 *   admin_label = @Translation("Crisis watch"),
 *   category = @Translation("Custom"),
 * )
 */
class CrisisWatchBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => $this->t('Hello, World!'),
    );
  }
}