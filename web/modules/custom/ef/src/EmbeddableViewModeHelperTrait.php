<?php

namespace Drupal\ef;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A trait that allows using classes to more easily grab the settings that we
 * store on the view display for embeddables
 *
 * @package Drupal\ef
 */
trait EmbeddableViewModeHelperTrait {

  protected function getThirdPartySettingForEmbeddableBundle ($embeddableBundleName, $settingName) {
    $result = [];

    /** @var EntityDisplayRepositoryInterface $entityDisplayRepository */
    $entityDisplayRepository = \Drupal::service('entity_display.repository');

    $view_modes = $entityDisplayRepository->getViewModeOptionsByBundle('embeddable', $embeddableBundleName);

    /** @var EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = \Drupal::service('entity_type.manager');

    /** @var EntityStorageInterface $storage */
    $storage = $entityTypeManager->getStorage('entity_view_display');

    foreach($view_modes as $view_mode => $val) {
      /** @var EntityViewDisplayInterface $view_display */
      $view_display = $storage->load('embeddable.' . $embeddableBundleName . '.' . $view_mode);
      $result[$view_mode] = $view_display->getThirdPartySetting('ef', $settingName);
    }

    return $result;
  }

  protected function getThirdPartySettingForEmbeddableBundleAndViewMode ($embeddableBundleName, $view_mode, $settingName)  {
    if ($this->isEmbeddableViewMode($view_mode)) {
      $all = $this->getThirdPartySettingForEmbeddableBundle ($embeddableBundleName, $settingName);

      if (isset($all[$view_mode])) {
        return $all[$view_mode];
      }
    }
  }

  /**
   * Given a list of view modes convert them into a nicely named associative
   * array
   *
   * @param $embeddableBundleName The embeddable bundle.
   * @param array $viewModes A simple list of view mode IDs.
   * @return array Associative array keyed by the view mode id and with the values
   *   of editor-friendly names.
   */
  protected function convertToEditorFriendlyAssociativeArray ($embeddableBundleName, array $viewModes) {
    $result = [];
    $editorFriendlyNames = $this->getThirdPartySettingForEmbeddableBundle($embeddableBundleName, 'editor_friendly_name');

    /** @var EntityDisplayRepositoryInterface $entityDisplayRepository */
    $entityDisplayRepository = \Drupal::service('entity_display.repository');

    $viewModeBundleOptions = $entityDisplayRepository->getViewModeOptionsByBundle('embeddable', $embeddableBundleName);

    $is_admin = in_array('administrator', \Drupal::currentUser()->getRoles());

    foreach ($viewModes as $viewMode) {
      if (isset($editorFriendlyNames[$viewMode]) && strlen($editorFriendlyNames[$viewMode]) > 0) {
        $displayName = $editorFriendlyNames[$viewMode];
      } else if (isset($viewModeBundleOptions[$viewMode])) {
        $displayName = $viewModeBundleOptions[$viewMode];
      } else {
        $displayName = \Drupal::translation()->t('Unknown');
      }

      if (true) {
        $displayName = sprintf ("%s (%s)", $displayName, $viewMode);
      }
      $result[$viewMode] = $displayName;
    }

    return $result;
  }

  /**
   * Verifies whether a view mode is a legitimate EF view mode.
   *
   * @param $viewMode string The ID of the viw mode
   * @return boolean
   */
  protected function isEmbeddableViewMode($viewMode) {
    return $viewMode == 'default' || strpos($viewMode, 'ef_variation_') === 0;
  }

  /**
   * Return an array of view mode names for the supplied embeddable bundle that
   * are view mode that are considered suitable for embedding.
   *
   * @param $embeddableBundleName
   * @return array
   */
  protected function getEmbeddableViewModes ($embeddableBundleName) {
    $result = [];

    /** @var EntityDisplayRepositoryInterface $entityDisplayRepository */
    $entityDisplayRepository = \Drupal::service('entity_display.repository');

    $view_modes = $entityDisplayRepository->getViewModeOptionsByBundle('embeddable', $embeddableBundleName);

    foreach($view_modes as $view_mode => $val) {
      if ($this->isEmbeddableViewMode($view_mode)) {
        $result[] = $view_mode;
      }
    }

    $result = $this->convertToEditorFriendlyAssociativeArray($embeddableBundleName, $result);

    return $result;
  }
}