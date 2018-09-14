<?php

namespace Drupal\ef\Theme;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class ThemeClassHelper
 *
 */
class ThemeCSSLibraryHelper implements ThemeCSSLibraryHelperInterface {
  /** @var EntityDisplayRepositoryInterface $entityDisplayRepository */
  var $entityDisplayRepository;

  /** @var EntityTypeBundleInfoInterface */
  var $entityTypeBundleInfo;

  /** @var ThemeManagerInterface  */
  var $themeManager;

  public function __construct(EntityDisplayRepositoryInterface $entityDisplayRepository, EntityTypeBundleInfoInterface $entityTypeBundleInfo, ThemeManagerInterface $themeManager) {
    $this->entityDisplayRepository = $entityDisplayRepository;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->themeManager = $themeManager;
  }

  public function onLibraryInfoBuild () {
    $libraries = [];

    $libraries += $this->generateThemeLibrariesForType ('embeddable');

    return $libraries;
  }

  /**
   * For the supplied type look in the active theme to see if any CSS files
   * exists for this type. As we wish to have pretty granular partial CSS files
   * we look by entity bundle and per view mode. If we detect that files of
   * that name exist then we add a library. This library adheres to a pattern
   * that makes it easy to attach to a render array.
   *
   * For example:
   *
   * If the embeddable type is passed in then this code loops through all the
   * bundles of the type. Let's imagine pull_quote is a bundle. Let's imagine
   * that only the default view mode is enabled for pull quote.
   *
   * It will look in the active theme for a file called pull_quote.default.css
   * in a folder called css/embeddable. In other works, it will look for a file
   * at [theme_root]/css/[entity_type]/[bundle_id].[view_mode].css.
   *
   * If it finds that file then it will create a library entry for it. The library
   * entry would be ef.embeddable.pull_quote.default. Or put generally
   * ef.[entity_type].[bundle_id].[view_mode].
   *
   * @param string $entity_type_id
   * @return array
   */
  protected function generateThemeLibrariesForType (string $entity_type_id) {
    $result = [];
    $all_bundle_info = $this->entityTypeBundleInfo->getAllBundleInfo();

    /** @var \Drupal\Core\Theme\ActiveTheme $activeTheme */
    $activeTheme = $this->themeManager->getActiveTheme();

    $theme_path = $activeTheme->getPath();

    if (isset($all_bundle_info[$entity_type_id])) {
      foreach ($all_bundle_info[$entity_type_id] as $bundle_name => $details) {
        $view_mode_options_for_bundle = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $bundle_name);

        foreach ($view_mode_options_for_bundle as $view_mode_name => $view_mode_display_name) {
          $css_file_path = sprintf('%s/css/%s/%s.%s.css', $theme_path, $entity_type_id, $bundle_name, $view_mode_name);
          if (file_exists($css_file_path)) {
            $id = sprintf('%s.%s.%s', $entity_type_id, $bundle_name, $view_mode_name);
            $result += [
              $id => [
                'version' => '1.x',
                'css' => [
                  'theme' => [
                    '/' . $css_file_path => []
                  ],
                ],
              ],
            ];
          }
        }
      }
    }
    return $result;
  }
}