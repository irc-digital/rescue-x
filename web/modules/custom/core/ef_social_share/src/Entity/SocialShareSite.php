<?php

namespace Drupal\ef_social_share\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\ef_social_share\SocialShareSiteConfigEntityInterface;
use Drupal\ef_social_share\SocialShareSitePluginCollection;

/**
 * Defines the configured social share entity.
 *
 * @ConfigEntityType(
 *   id = "social_share_site",
 *   label = @Translation("Social share site"),
 *   label_collection = @Translation("Social share sites"),
 *   label_singular = @Translation("social share site"),
 *   label_plural = @Translation("social share site"),
 *   label_count = @PluralTranslation(
 *     singular = "@count social share site",
 *     plural = "@count social share sites",
 *   ),
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "plugin",
 *     "configuration",
 *   }
 * )
 */
class SocialShareSite extends ConfigEntityBase implements SocialShareSiteConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * The name (plugin ID) of the social share site.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the social share site.
   *
   * @var string
   */
  protected $label;

  /**
   * The configuration of the social share site plugin.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * The plugin ID of the social share site plugin.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin collection that stores social share plugins.
   *
   * @var \Drupal\ef_social_share\SocialShareSitePluginCollection
   */
  protected $pluginCollection;

  /**
   * Encapsulates the creation of the social share site's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The social share site's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $this->pluginCollection = new SocialShareSitePluginCollection(\Drupal::service('plugin.manager.social_share_sites'), $this->plugin, $this->configuration);
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['configuration' => $this->getPluginCollection()];
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin($plugin_id) {
    $this->plugin = $plugin_id;
    $this->getPluginCollection()->addInstanceId($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->getPlugin()->getPluginDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $entities) {
    return $this->getPlugin()->executeMultiple($entities);
  }
  
}
