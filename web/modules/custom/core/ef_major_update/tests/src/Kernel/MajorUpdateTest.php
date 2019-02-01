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

  public function testMajorlyUpdatedFieldIsEmptyforNewNodesWithFieldOffForm() {
    $node = Node::create([
      'title' => 'Sampe node',
      'type' => 'test_field_off_form',
      'status' => TRUE,
    ]);

    $node->save();
  }
}