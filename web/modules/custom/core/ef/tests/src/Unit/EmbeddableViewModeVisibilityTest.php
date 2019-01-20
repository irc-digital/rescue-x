<?php
namespace Drupal\Tests\ef\Unit;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\ef\EmbeddableViewModeVisibilityServiceInterface;
use Drupal\ef\EmbeddableViewModeVisibility;
use Drupal\ef\Plugin\EmbeddableViewModeVisibilityPluginManager;
use Drupal\Tests\UnitTestCase;

/**
 * Class EmbeddableViewModeVisibilityTest
 *
 * @coversDefaultClass \Drupal\ef\EmbeddableViewModeVisibility
 * @package Drupal\Tests\ef\Unit
 * @group ef
 */
class EmbeddableViewModeVisibilityTest extends UnitTestCase {

  /** @var EmbeddableViewModeVisibilityServiceInterface*/
  private $embeddableViewModeVisibility;

  public function setUp() {
    /** @var EmbeddableViewModeVisibilityPluginManager $embeddableViewModeVisibilityPluginManager */
    $embeddableViewModeVisibilityPluginManager = $this->createMock('Drupal\ef\Plugin\EmbeddableViewModeVisibilityPluginManager');

    $entityTypeBundleInfo = $this->createMock('Drupal\Core\Entity\EntityTypeBundleInfoInterface');

    $this->embeddableViewModeVisibility = new EmbeddableViewModeVisibility($embeddableViewModeVisibilityPluginManager, $entityTypeBundleInfo);
    $expectedDefinitions = array(
      'field' => array(
        'id' => 'field',
        'label' => 'Field'
      ),
    );
    $embeddableViewModeVisibilityPluginManager->method('getDefinitions')->willReturn($expectedDefinitions);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructor () {
    $this->assertTrue ($this->embeddableViewModeVisibility instanceof EmbeddableViewModeVisibilityServiceInterface);
  }

  /**
   * @covers ::getViewModeVisibilityOptions
   */
  public function testGetViewModeVisibilityOptions () {
    $getViewModeVisibilityOptions = $this->embeddableViewModeVisibility->getViewModeVisibilityOptions();
    $this->assertArrayEquals(['field' => 'Field'], $getViewModeVisibilityOptions);
  }

}