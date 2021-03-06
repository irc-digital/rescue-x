<?php

namespace Drupal\Tests\ef_social_share;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class SocialSharePluginManagerTest
 *
 * @coversDefaultClass \Drupal\ef_social_share\SocialShareSitesManager
 *
 * @package Drupal\Tests\ef
 */
class SocialSharePluginManagerTest extends KernelTestBase {
  public static $modules = ['system', 'link', 'field', 'language', 'text', 'ui_patterns', 'content_translation', 'user', 'node', 'ef_patterns', 'ef_icon_library', 'ef_sitewide_settings', 'ef_social_share', 'ef_social_share_test'];

  /**
   * @var \Drupal\ef_social_share\SocialShareSitesManager
   */
  protected $socialShareSitesManager;

  public function setUp() {
    parent::setUp();

    $this->installConfig([
      'system',
      'field',
      'text',
      'node',
      'ef_social_share',
      'ef_social_share_test'
    ]);

    $this->installEntitySchema('sitewide_settings');

    $this->socialShareSitesManager = $this->container->get('plugin.manager.social_share_sites');
  }

  public function testSocialSharePluginDiscovery () {
    $definitions = $this->socialShareSitesManager->getDefinitions();

    $this->assertArrayHasKey('test_social_share_site', $definitions);
  }

}