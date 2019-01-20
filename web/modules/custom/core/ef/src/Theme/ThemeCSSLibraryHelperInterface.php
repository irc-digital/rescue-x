<?php

namespace Drupal\ef\Theme;

/**
 * Class ThemeClassHelper
 *
 * This class is fired from library_info_build and looks at the currently active
 * theme and searches for entity and view mode specific CSS files. This is helpful
 * for delivery CSS efficiently, but not putting the onus on the theme to always
 * remember to have to extend/register the library - basically saves some
 * boilerplate code and just let's the developer put a CSS file in a specific
 * directory and it be used
 */
interface ThemeCSSLibraryHelperInterface {
  public function onLibraryInfoBuild ();
}