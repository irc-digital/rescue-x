<?php

namespace Drupal\ef_icon_library;

/**
 * Interface EFIconLibraryInterface
 *
 * Icon library service interface
 *
 * @package Drupal\ef
 */
interface IconLibraryInterface {
  public function getIconList ();

  public function getIconInformation ($key, $mark_as_in_use = FALSE);

  public function patternIsBeingRendered ($variables);

  public function getInUseIcons ();
}