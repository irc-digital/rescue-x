<?php

namespace Drupal\ef;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\ef\Plugin\EmbeddableViewModeVisibilityPluginManager;

/**
 * Class EmbeddableViewModeVisibility
 * @package Drupal\ef
 */
class EmbeddableViewModeVisibility implements EmbeddableViewModeVisibilityServiceInterface {

  use EmbeddableViewModeHelperTrait;

  /** @var \Drupal\ef\Plugin\EmbeddableViewModeVisibilityPluginManager  */
  private $embeddableViewModeVisibilityPluginManager;

  /** @var EntityTypeBundleInfoInterface */
  private $entityTypeBundleInfo;

  function __construct(EmbeddableViewModeVisibilityPluginManager $embeddableViewModeVisibilityPluginManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    $this->embeddableViewModeVisibilityPluginManager = $embeddableViewModeVisibilityPluginManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * @inheritdoc
   */
  function getViewModeVisibilityOptions() {
    $options = [];

    /** @var array */
    $visibilityDefinitions = $this->embeddableViewModeVisibilityPluginManager->getDefinitions();

    foreach ($visibilityDefinitions as $visibilityDefinition) {
      $options[$visibilityDefinition['id']] = $visibilityDefinition['label'];
    }

    return $options;
  }

  /**
   * @inheritdoc
   */
  function getVisibleViewModes($embeddableBundle, $option) {
    $result = [];

    /** @var array */
    $visibilityDefinitions = $this->embeddableViewModeVisibilityPluginManager->getDefinitions();

    $id = NULL;

    foreach ($visibilityDefinitions as $visibilityDefinition) {
      if ($visibilityDefinition['class'] == $option) {
        $id = $visibilityDefinition['id'];
        break;
      }
    }

    if (!is_null($id)) {
      $viewModeVisibility = $this->getThirdPartySettingForEmbeddableBundle ($embeddableBundle, 'view_mode_visibility');

      foreach ($viewModeVisibility as $viewMode => $visibility) {
        if (in_array($id, $visibility) && $this->isEmbeddableViewMode($viewMode)) {
          $result[] = $viewMode;
        }
      }
      $result = $this->convertToEditorFriendlyAssociativeArray($embeddableBundle, $result);
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  function getAllVisibleBundles($option) {
    $result = [];

    $embeddable_bundles_info = $this->entityTypeBundleInfo->getBundleInfo('embeddable');

    foreach ($embeddable_bundles_info as $bundle_key => $embeddable_bundle_info) {
      $view_modes = $this->getVisibleViewModes($bundle_key, $option);

      if (count($view_modes) > 0) {
        $result[] = $bundle_key;
      }
    }

    return $result;
  }


}