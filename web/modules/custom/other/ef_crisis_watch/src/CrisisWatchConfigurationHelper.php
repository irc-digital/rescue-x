<?php

namespace Drupal\ef_crisis_watch;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CrisisWatchConfigurationHelper
 *
 * A helper classed used at module-enabling time to ensure that the crisis watch
 * link field includes all bundles in it reference bundle list
 *
 * @package Drupal\ef_crisis_watch
 */
class CrisisWatchConfigurationHelper implements ContainerInjectionInterface {

  /**
   * The entity type bundle manager
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The configuration factory
   *
   * @var ConfigFactoryInterface
   */
  protected $configurationFactory;

  public function __construct(ConfigFactoryInterface $configurationFactory, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->configurationFactory = $configurationFactory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * Make sure that when crisis watch is enabled all content types are added
   * to the crisis watch link field as available reference bundles types
   *
   * @return $this
   */
  function ensureAllContentTypesEnabledOnCrisisWatchLinkReferenceField () {

    $all_node_bundles = $this->entityTypeBundleInfo->getBundleInfo('node');

    /** @var \Drupal\field\FieldStorageConfigInterface $field_config */
    $field_config = FieldStorageConfig::loadByName('sitewide_settings', 'field_crisis_watch_link');

    if (!is_null($field_config)) {
      $bundle_keys = array_keys($all_node_bundles);
      $config_name = 'field.field.sitewide_settings.crisis_watch.field_crisis_watch_link';
      $field_crisis_watch_link_settings = $this->configurationFactory->getEditable($config_name)->get('settings');
      $current_bundle_keys = is_array($field_crisis_watch_link_settings['handler_settings']['target_bundles']) ? array_keys($field_crisis_watch_link_settings['handler_settings']['target_bundles']) : [];

      if ($bundle_keys != $current_bundle_keys) {
        $bundles = array_combine ($bundle_keys, $bundle_keys);
        $field_crisis_watch_link_settings['handler_settings']['target_bundles'] = $bundles;
        $this->configurationFactory->getEditable($config_name)->set('settings', $field_crisis_watch_link_settings)->save(TRUE);
      }

    }

    return $this;
  }

}