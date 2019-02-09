<?php

namespace Drupal\ef\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Annotation\EntityReferenceSelection;
use Drupal\Core\Language\LanguageInterface;
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

    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];
    $entity_type = $this->entityManager->getDefinition($target_type);

    $language_manager = \Drupal::languageManager();

    $current_language = $language_manager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);

    $query->condition($entity_type->getKey('langcode'), $current_language->getId(), '=');

    return $query;
  }
}