<?php

namespace Drupal\Tests\ef_reach_through_content;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class DependentEmbeddableServiceTest
 *
 * @coversDefaultClass \Drupal\ef_reach_through_content\ReachThroughService
 *
 * @package Drupal\Tests\ef
 */
class ReachThroughServiceTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'language', 'text', 'content_translation', 'user', 'node', 'ef_mandatory_field_summary', 'ef_reach_through_content', 'ef_reach_through_content_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig([
      'system',
      'field',
      'text',
      'node',
      'ef_reach_through_content',
      'ef_reach_through_content_test'
    ]);
    $this->installEntitySchema('user');
  }

  /**
   * @covers ::geReachThroughFields
   */
  public function testGeReachThroughFields () {

  }
}