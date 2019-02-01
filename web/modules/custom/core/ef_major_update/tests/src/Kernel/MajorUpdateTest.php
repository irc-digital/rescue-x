<?php

namespace Drupal\Tests\ef_major_update;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;

/**
 * @group ef
 */
class MajorUpdateTest extends KernelTestBase {

  /**
   * Modules to install.
   * @var array
   */
  public static $modules = ['system','field', 'language', 'content_translation', 'node', 'ef', 'ef_major_update', 'ef_major_update_test', 'text', 'user'];

  public function setUp() {
    parent::setUp();
    $this->installConfig(['user', 'node','system','language','field', 'ef_major_update', 'ef_major_update_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('embeddable_relation');
  }



  public function testMajorlyUpdatedFieldForNewNode() {
    $node = Node::create([
      'title' => 'Test node - field off form',
      'body' => 'body text',
      'type' => 'test_field_off_form',
      'major_update' => TRUE,
      'status' => TRUE,
    ]);

    $node->save();

    $majorly_updated = $node->field_majorly_updated->value;
    $this->assertNull($majorly_updated);
  }
}