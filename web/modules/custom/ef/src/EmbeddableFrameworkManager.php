<?php

namespace Drupal\ef;

use Drupal\Core\Config\ConfigFactory;
use Drupal\ef\Entity\EmbeddableType;

/**
 * Implementation of the EmbeddableFrameworkManagerInterface
 *
 * Class EmbeddableFrameworkManager
 * @package Drupal\ef
 */
class EmbeddableFrameworkManager implements EmbeddableFrameworkManagerInterface  {
  /** @var  ConfigFactory */
  private $configFactory;

  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }


  /**
   * @inheritdoc
   */
  public function getConfigSetting ($setting_name) {
    $configFactory = \Drupal::configFactory();
    $embeddableFrameworkSettings = $configFactory->get('ef.settings');

    return $embeddableFrameworkSettings->get($setting_name);
  }

  /**
   * @inheritdoc
   */
  public function getEmbeddableTypesNotExcludedFromEmbeddableOverviewQuickAddList () {
    $result = [];

    $allEmbeddableTypes = EmbeddableType::loadMultiple();

    /** @var EmbeddableTypeInterface $embeddableType */
    foreach ($allEmbeddableTypes as $embeddableType) {
      if (!$embeddableType->isExcludedFromEmbeddableOverviewQuickAddList()) {
        $result[] = $embeddableType;
      }
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  public function addButtonsToEmbeddableOverviewPage (&$local_actions) {
    $maximumButtonCount = $this->getConfigSetting('ui.embeddable_content_overview_add_max');

    $allEmbeddableTypes = $this->getEmbeddableTypesNotExcludedFromEmbeddableOverviewQuickAddList();
    if ($maximumButtonCount == -1 || sizeof($allEmbeddableTypes) <= $maximumButtonCount) {
      /** @var EmbeddableTypeInterface $embeddableType */
      foreach ($allEmbeddableTypes as $embeddableType) {
        $machineName = $embeddableType->id();
        $entryId = 'entity.embeddable.add_' . $machineName;

        if (!isset($local_actions[$entryId])) {
          $local_actions[$entryId] = [
            'id' => $entryId,
            'title' => t($embeddableType->label()),
            'weight' => 0,
            'route_name' => 'entity.embeddable.add',
            'route_parameters' => [
              'embeddable_type' => $machineName,
            ],
            'options' => [],
            'appears_on' => ['entity.embeddable.collection'],
            'class' => 'Drupal\\Core\\Menu\\LocalActionDefault',
            'provider' => 'ef',
          ];
        }
      }
      return;
    }

    // if we got here, we with need to display the general 'add' button
    $local_actions['entity.embeddable.add_page'] = [
      'id' => 'entity.embeddable.add_page',
      'title' => t('Add embeddable'),
      'weight' => '',
      'route_name' => 'entity.embeddable.add_page',
      'appears_on' => ['entity.embeddable.collection'],
      'class' => 'Drupal\\Core\\Menu\\LocalActionDefault',
      'options' => [],
      'provider' => 'ef',
    ];
  }

}