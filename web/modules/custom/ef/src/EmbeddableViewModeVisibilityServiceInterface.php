<?php

namespace Drupal\ef;

use Drupal\ef\Plugin\EmbeddableViewModeVisibilityInterface;

/**
 * Interface EmbeddableViewModeVisibilityServiceInterface
 *
 * Within the Embeddable Framework we can permit only certain view modes
 * to be accessible in certain context.  This is useful because landing
 * pages are constructed as row-based entries and some modes that work fine
 * within, say, a WYSIWYG do not work well as a row-based element. For example,
 * an image that is floated left or right is great when there is some text
 * to wrap around, but that mode would not work well as a row-based element.
 *
 * This interaces allows us to retrieve the entire universe of visibility
 * spots (i.e. field, WYSIWYG) and also to get the select mode for a given
 * bundle
 *
 * @package Drupal\ef
 */
interface EmbeddableViewModeVisibilityServiceInterface {
  /**
   * Gets the visibility mode options. These are harvested from EmbeddableViewModeVisibilityInterface
   * plugins
   *
   * @return array EmbeddableViewModeVisibilityInterface
   */
  function getViewModeVisibilityOptions();

  /**
   * Retries the view modes that have been selected for the provided bundled
   * that match the supplied option
   *
   * @param string $embeddableBundle
   * @param $option EmbeddableViewModeVisibilityInterface class
   * @return array An array of view modes that are marked as enabled for the
   *   supplied mode option
   */
  function getVisibleViewModes($embeddableBundle, $option);

  /**
   * For the supplied visibility mode option (e.g. field or WYSIWYG) returns
   * all the embeddable bundles configured to use that type. This does not
   * discern across view modes - so long as there is at least one view mode
   * for the bundle that uses the supplied option, it will be included in the
   * return array
   *
   * @param $option
   * @return array of embeddable bundle names
   */
  function getAllVisibleBundles ($option);
}