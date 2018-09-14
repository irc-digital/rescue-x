<?php

namespace Drupal\Tests\ef\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\ef\EmbeddableReferenceMode;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class EmbeddableReferenceModeTest
 *
 * @coversDefaultClass \Drupal\ef\EmbeddableReferenceMode
 * @package Drupal\Tests\ef\Unit
 *
 * @group ef
 */
class EmbeddableReferenceModeTest extends UnitTestCase {

  /** @var EmbeddableReferenceModeInterface */
  private $embeddableReferenceMode;

  /**
   * Current user proxy mock.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  public function setUp() {
    parent::setUp();

    $mock_translation = $this->getStringTranslationStub();
    $currentUser = $this->createMock('\Drupal\Core\Session\AccountProxyInterface');

    $container = new ContainerBuilder();
    $container->set('current_user', $currentUser);
    \Drupal::setContainer($container);

    $this->currentUser = \Drupal::currentUser();

    $this->embeddableReferenceMode = new EmbeddableReferenceMode($mock_translation);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructor () {
    $this->assertTrue ($this->embeddableReferenceMode instanceof EmbeddableReferenceModeInterface);
  }

  /**
   * @covers ::getModes
   */
  public function testGetModes () {

    $modes = $this->embeddableReferenceMode->getModes();

    $expected = [
      EmbeddableReferenceModeInterface::ENABLED => 'Enabled',
      EmbeddableReferenceModeInterface::TEST => 'Test',
      EmbeddableReferenceModeInterface::DISABLED => 'Disabled',
    ];

    $this->assertArrayEquals($expected, $modes);
  }

  /**
   * @covers ::getDefaultMode
   */
  public function testGetDefaultModeIsALegitMode () {
    $default_mode = $this->embeddableReferenceMode->getDefaultMode();

    $available_options = $this->embeddableReferenceMode->getModes();

    $this->assertArrayHasKey($default_mode, $available_options);

  }

  /**
   * The data fed to the testAccessTestState test
   */
  public function getAccessToTestStates() {
    return [
      [TRUE, EmbeddableReferenceModeInterface::ENABLED, TRUE],
      [TRUE, EmbeddableReferenceModeInterface::ENABLED, FALSE],
      [FALSE, EmbeddableReferenceModeInterface::DISABLED, TRUE],
      [FALSE, EmbeddableReferenceModeInterface::DISABLED, FALSE],
      [TRUE, EmbeddableReferenceModeInterface::TEST, TRUE],
      [FALSE, EmbeddableReferenceModeInterface::TEST, FALSE],
    ];
  }

  /**
   * @covers ::getAccess
   * @dataProvider getAccessToTestStates
   */
  public function testAccess ($expected, $mode, $has_permission_to_view_embeds_marked_as_test) {
    $this->currentUser->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap([
        [EmbeddableReferenceModeInterface::TEST_MODE_PERMISSION_NAME, $has_permission_to_view_embeds_marked_as_test],
      ]);

    $this->assertEquals($expected, $this->embeddableReferenceMode->getAccess($mode));
  }
}

