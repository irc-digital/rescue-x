<?php

namespace Drupal\ef;

/**
 * Interface EmbeddableFrameworkManagerInterface
 *
 * Defines some helper functions for use when using the EF
 *
 * @package Drupal\ef
 */
interface EmbeddableFrameworkManagerInterface {
  /**
   * Gets an embeddable framework-wide settings
   *
   * @param string $setting_name the setting name/id
   * @return mixed
   */
  function getConfigSetting ($setting_name);

  /**
   * Returns the list of embeddable types that should be displayed on the
   * quick add list
   *
   * @return array
   */
  function getEmbeddableTypesNotExcludedFromEmbeddableOverviewQuickAddList ();


  /**
   * Modifies the supplied local action array to include the buttons added to
   * the top of the embeddable overivew page
   *
   * @param $local_actions
   * @return mixed
   */
  function addButtonsToEmbeddableOverviewPage (&$local_actions);
}