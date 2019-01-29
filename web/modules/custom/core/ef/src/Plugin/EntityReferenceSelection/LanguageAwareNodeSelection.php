<?php

namespace Drupal\ef\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Annotation\EntityReferenceSelection;
use Drupal\node\Plugin\EntityReferenceSelection\NodeSelection;

/**
 * Extends the node selection to filter the items in the list to the language on the page
 *
 * @EntityReferenceSelection(
 *   id = "node_language_aware",
 *   label = @Translation("Node selection - language aware (custom)"),
 *   entity_types = {"node"},
 *   group = "node_language_aware",
 *   weight = 5
 * )
 */
class LanguageAwareNodeSelection extends NodeSelection {
  /**
   * @inheritdoc
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    $language_manager = \Drupal::languageManager();

    $current_language = $language_manager->getCurrentLanguage();

    $query->condition('langcode', $current_language->getId(), '=');

    return $query;
  }
}