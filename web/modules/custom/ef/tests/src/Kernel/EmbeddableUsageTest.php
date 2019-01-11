<?php

namespace Drupal\Tests\ef;

use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Entity\EmbeddableRelation;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddableUsageTest
 *
 * @coversDefaultClass \Drupal\ef\EmbeddableUsageService
 * @package Drupal\Tests\ef
 *
 * @group ef
 */
class EmbeddableUsageTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['system', 'field', 'filter', 'text', 'node', 'ef', 'ef_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
  }

  /**
   * @covers ::onInsert
   * @covers ::onChange
   * @covers ::onDelete
   * @covers ::onUpdate
   * @covers ::removeCurrentRelations
   * @covers ::addNewRelations
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUsageServiceForDummyUsagePlugin () {
    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_test', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    // first time around we should have three relations
    /** @see \Drupal\ef_test\Plugin\EmbeddableUsage\TestEmbeddableUsage */
    $this->assertCount(3, $relations);

    // resave to trigger the hook to fire again
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_test','=')
      ->execute();

    // second time just two (as per the hardcode rules in TestEmbeddableUsage
    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);
    $this->assertCount(2, $relations);

    // now delete the referrer and make sure that the reference disappear
    $referer->delete();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_test','=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);
    $this->assertCount(0, $relations);
  }

  /**
   * Flow: create three embeddable add them to the refer. Check that we have
   * three EntityRelation objects created. Then remove one, and check that we
   * have only two relations. Then remove them all and make sure the relations
   * disappear
   *
   * @covers ::onInsert
   * @covers ::onChange
   * @covers ::onDelete
   * @covers ::onUpdate
   * @covers ::removeCurrentRelations
   * @covers ::addNewRelations
   * @covers \Drupal\ef\Plugin\EmbeddableUsage\EntityReferenceEmbeddableItemUsage::getUsedEmbeddableEntities
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUsageServiceForEmbeddableReferencePluginMultiValue () {
    // use the multivalue field first
    $reference_1 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);
    $reference_1->save();

    $reference_2 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);
    $reference_2->save();

    $reference_3 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);
    $reference_3->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
      'field_embeddable_references' => [
        ['target_id' => $reference_1->id()],
        ['target_id' => $reference_2->id()],
        ['target_id' => $reference_3->id()],
      ],
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_references', '=')
      ->condition('embeddable_id', [$reference_1->id(), $reference_2->id(), $reference_3->id()], 'IN')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

    // remove one of the reference
    $referer->field_embeddable_references = [
      ['target_id' => $reference_1->id()],
      ['target_id' => $reference_3->id()],
    ];
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_references', '=')
      ->condition('embeddable_id', [$reference_1->id(), $reference_3->id()], 'IN')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(2, $relations);

    // remove them all and check again
    $referer->field_embeddable_references = NULL;
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_references', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(0, $relations);
  }

  /**
   * Flow: create an embeddable add it to the refer single value fiedl. Check that we have
   * one EntityRelation object created that points to the embeddable. Then
   * switch it to another embeddable reference and check again. Then remove
   * the reference and check that no relations remain
   *
   * @covers \Drupal\ef\Plugin\EmbeddableUsage\EntityReferenceEmbeddableItemUsage::getUsedEmbeddableEntities
   * @covers ::onInsert
   * @covers ::onChange
   * @covers ::onDelete
   * @covers ::onUpdate
   * @covers ::removeCurrentRelations
   * @covers ::addNewRelations
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */

  public function testUsageServiceForEmbeddableReferencePluginSingleValue () {
    $reference_1 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);
    $reference_1->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
      'field_embeddable_reference' => [
        'target_id' => $reference_1->id()
      ],
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_reference', '=')
      ->condition('embeddable_id', $reference_1->id(), '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(1, $relations);

    $reference_2 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 2',
    ]);

    $reference_2->save();

    $referer->field_embeddable_reference = [
      'target_id' => $reference_2->id()
    ];

    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_reference', '=')
      ->condition('embeddable_id', $reference_2->id(), '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(1, $relations);

    $referer->field_embeddable_reference = NULL;
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_embeddable_reference', '=')
      ->condition('embeddable_id', $reference_2->id(), '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(0, $relations);
  }
}

