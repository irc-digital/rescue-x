<?php

/**
 * @file
 * Contains reach_through.page.inc.
 *
 * Page callback for Reach-through entry entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Reach-through entry templates.
 *
 * Default template: reach_through.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_reach_through(array &$variables) {
  // Fetch ReachThrough Entity Object.
  $reach_through = $variables['elements']['#reach_through'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
