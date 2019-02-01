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
    $this->installConfig(['user','text','node','system','language','field', 'ef_major_update', 'ef_major_update_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('embeddable_relation');
    $this->installSchema('node', 'node_access');
  }

  public function testMajorlyUpdatedFieldForNewNode() {
    $node = $this->createTestNode('test_field_off_form',TRUE);
    $majorly_updated = $node->field_majorly_updated->value;
    $this->assertNull($majorly_updated);
  }

  public function testMajorlyUpdatedFieldForEditedNodes() {
    $content_types = array('test_field_off_form', 'test_field_on_form');
    foreach ($content_types as $content_type) {
      $node = $this->createTestNode($content_type);
      $node->major_update = TRUE;
      $node->save();
      $majorly_updated = $node->field_majorly_updated->value;
      $this->assertNotNull($majorly_updated);
    }
  }

  protected function createTestNode($type) {
    $node = Node::create([
      'type' => $type,
      'title' => 'My god...',
      'major_update' => FALSE,
    ]);

    $node->save();

    return $node;
  }

  function testMajorlyUpdatedFieldWithMultipleLanguages () {
    // Create default english node
    $node = $this->createTestNode('test_field_off_form');
    $node->save();

    // Create Spanish version
    $node_es = $node->addTranslation('es');
    $node_es->title = 'Dios mio!';
    $node_es->save();

    // Re-save english node with major update
    $node->major_update = TRUE;
    $node->save();

    $majorly_updated_en = $node->field_majorly_updated->value;
    $majorly_updated_es = $node_es->field_majorly_updated->value;

    $this->assertNotEquals($majorly_updated_en, $majorly_updated_es);
  }

}