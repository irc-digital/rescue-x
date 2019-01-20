<?php

namespace Drupal\Tests\ef\Unit\Entity;

use Drupal\ef\Entity\Embeddable;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddableTest
 *
 * @coversDefaultClass \Drupal\ef\Entity\Embeddable
 * @package Drupal\Tests\ef\Unit\Entity
 *
 * @group ef
 */
class EmbeddableTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['ef', 'ef_test']);
  }

  /**
   * @covers ::getTitle
   * @covers ::getType
   * @covers ::setTitle
   * @covers ::baseFieldDefinitions
   */
  public function testEmbeddable () {
    $testEmbeddable = Embeddable::create([
      'type' => 'test',
      'title' => 'Test title',
      'uid' => 0,
    ]);

    $this->assertEquals('Test title', $testEmbeddable->getTitle());
    $this->assertEquals('test', $testEmbeddable->getType());

    $testEmbeddable->setTitle('New title');
    $this->assertEquals('New title', $testEmbeddable->getTitle());
  }
}

