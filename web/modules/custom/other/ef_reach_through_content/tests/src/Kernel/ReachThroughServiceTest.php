<?php

namespace Drupal\Tests\ef_reach_through_content;

use Drupal\ef_reach_through_content\Entity\ReachThrough;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;

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
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('reach_through');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
  }

  /**
   * @covers ::geReachThroughFields
   */
  public function testGeReachThroughFields () {
    /** @var \Drupal\ef_reach_through_content\ReachThroughServiceInterface $reachThroughService */
    $reachThroughService = \Drupal::service('ef.reach_through_service');

    $fields = $reachThroughService->geReachThroughFields('test_reach_through_type');

    $this->assertEquals(['field_title' => 'Title'], $fields);
  }

  protected function addFullyMappedNodeMonolingual () {
    $node = Node::create([
      'type' => 'test',
      'title' => 'Title for test node',
    ]);

    $node->save();

    $stored_reach_throughs = ReachThrough::loadMultiple();

    $this->assertCount(1, $stored_reach_throughs);

    /** @var \Drupal\ef_reach_through_content\Entity\ReachThroughInterface $reach_through */
    $reach_through = reset($stored_reach_throughs);

    $wrapped_node = $reach_through->reach_through_ref->entity;

    $this->assertNotNull($wrapped_node);

    $this->assertEquals($node->id(), $wrapped_node->id());

    $this->assertEquals('Test: Title for test node', $reach_through->getName());

    return $node;
  }

  /**
   * @covers ::onInsert
   */
  public function testAddingFullyMappedNodeMonolingual () {
    $this->addFullyMappedNodeMonolingual();
  }

  /**
   * @covers ::onUpdate
   */
  public function testUpdatingFullyMappedNodeMonolingual () {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->addFullyMappedNodeMonolingual();

    $node->setTitle("Modified title");
    $node->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $reach_through_storage */
    $reach_through_storage = \Drupal::service('entity_type.manager')->getStorage('reach_through');

    /** @var \Drupal\ef_reach_through_content\Entity\ReachThroughInterface $stored_reach_through */
    $stored_reach_through = $reach_through_storage->getQuery()
      ->condition('reach_through_ref', $node->id(), '=')
      ->execute();

    $this->assertCount(1, $stored_reach_through);
    $stored_reach_through = ReachThrough::load(key($stored_reach_through));

    $this->assertNotNull($stored_reach_through);

    $this->assertEquals('Test: Modified title', $stored_reach_through->getName());
  }

  /**
   * @covers ::onDelete
   */
  public function testDeleteFullyMappedNodeMonolingual () {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->addFullyMappedNodeMonolingual();

    $node->delete();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $reach_through_storage */
    $reach_through_storage = \Drupal::service('entity_type.manager')->getStorage('reach_through');

    /** @var \Drupal\ef_reach_through_content\Entity\ReachThroughInterface $stored_reach_through */
    $count = $reach_through_storage->getQuery()
      ->condition('reach_through_ref', $node->id(), '=')
      ->count()
      ->execute();

    $this->assertEquals(0, $count);

  }
}