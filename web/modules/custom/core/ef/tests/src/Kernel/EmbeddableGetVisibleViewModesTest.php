<?php

namespace Drupal\Tests\ef;

use Drupal\ef\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityField;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddableViewModeVisibilityTest
 *
 * @coversDefaultClass \Drupal\ef\EmbeddableViewModeVisibility
 * @package Drupal\Tests\ef
 * @group ef
 */
class EmbeddableGetVisibleViewModesTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['system', 'language','field', 'filter', 'text', 'node', 'ef', 'ef_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
  }


  /**
   * Check if sample testing bundle view modes are loaded properly
   *
   * @covers ::getVisibleViewModes
   */
  public function testGetVisibleViewModes () {
    /** @var \Drupal\ef\EmbeddableViewModeVisibility $viewModeVisibilityService */
    $viewModeVisibilityService = \Drupal::service('ef.view_mode_visibility');
    $viewModes = $viewModeVisibilityService->getVisibleViewModes('test', EmbeddableViewModeVisibilityField::class);
    $this->assertArrayHasKey('default', $viewModes);
    $this->assertArrayNotHasKey('ef_variation_1', $viewModes);
  }

  /**
   * Check if sample testing bundle view modes are loaded properly
   *
   * @covers ::getAllVisibleBundles
   */
  public function testGetAllVisibleBundles () {
    /** @var \Drupal\ef\EmbeddableViewModeVisibility $viewModeVisibilityService */
    $viewModeVisibilityService = \Drupal::service('ef.view_mode_visibility');
    $bundles = $viewModeVisibilityService->getAllVisibleBundles(EmbeddableViewModeVisibilityField::class);

    $this->assertCount(1, $bundles);
    $this->assertEquals(['test'], $bundles);
  }
}
