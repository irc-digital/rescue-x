<?php

namespace Drupal\Tests\ef\Unit\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Entity\EmbeddableRelation;
use Drupal\ef\Exception\DeleteInUseEmbeddableException;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class EmbeddableRelationTest
 *
 * @coversDefaultClass \Drupal\ef\Entity\EmbeddableRelation
 * @package Drupal\Tests\ef\Unit\Entity
 *
 * @group ef
 */
class EmbeddableRelationTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['ef', 'ef_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
  }

  /**
   * @covers ::baseFieldDefinitions
   * @covers ::preDelete
   * @covers \Drupal\ef\EmbeddableUsageService::isInUse
   */
  public function testEmbeddableRelationPreSaveForInUseEmbeddable () {
    /** @var \Drupal\ef\Entity\Embeddable $testEmbeddable */
    $testEmbeddable = Embeddable::create([
      'type' => 'test',
      'title' => 'Test title',
    ]);

    $testEmbeddable->save();

    $this->assertNotNull($testEmbeddable->id());

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $referer->save();

    $this->assertNotNull($referer->id());

    $relation = EmbeddableRelation::create([
      'embeddable_id' => $testEmbeddable->id(),
      'referring_id' => $referer->id(),
      'referring_type' => 'embeddable',
      'referring_field_name' => 'field_some_field_name',
    ]);

    $relation->save();

    $this->assertNotNull($relation->id());

    // attempt to delete the embeddable

    $exception_test_passed = FALSE;

    try {
      $testEmbeddable->delete();
    } catch (EntityStorageException $exception) {
      $exception_test_passed = TRUE;
    }

    $this->assertTrue($exception_test_passed);
  }

  /**
   * @covers ::baseFieldDefinitions
   * @covers ::preDelete
   * @covers \Drupal\ef\EmbeddableUsageService::isInUse
   * @covers \Drupal\ef\Exception\DeleteInUseEmbeddableException::getEmbeddable
   * @covers \Drupal\ef\Exception\DeleteInUseEmbeddableException::__construct
   */
  public function testThrownExceptionForInUseEmbeddable () {
    /** @var \Drupal\ef\Entity\Embeddable $testEmbeddable */
    $testEmbeddable = Embeddable::create([
      'type' => 'test',
      'title' => 'Test title',
      'status' => 1,
    ]);

    $testEmbeddable->save();

    $this->assertNotNull($testEmbeddable->id());

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $referer->save();

    $this->assertNotNull($referer->id());

    $relation = EmbeddableRelation::create([
      'embeddable_id' => $testEmbeddable->id(),
      'referring_id' => $referer->id(),
      'referring_type' => 'embeddable',
      'referring_field_name' => 'field_some_field_name',
    ]);

    $relation->save();

    $this->assertNotNull($relation->id());

    // attempt to delete the embeddable

    try {
      $testEmbeddable->delete();
    } catch (EntityStorageException $entityStorageException) {
      /** @var DeleteInUseEmbeddableException $previousException */
      $previousException = $entityStorageException->getPrevious();

      $this->assertNotNull($previousException);
      $this->assertTrue($previousException instanceof DeleteInUseEmbeddableException);

      /** @var \Drupal\ef\EmbeddableInterface $embeddable_in_use */
      $embeddable_in_use = $previousException->getEmbeddable();
      $this->assertNotNull($embeddable_in_use);

      $this->assertEquals($testEmbeddable->id(), $embeddable_in_use->id());
    }
  }

  /**
   * @covers \Drupal\ef\EmbeddableUsageService::isInUse
   * @covers ::baseFieldDefinitions
   * @covers ::preDelete
   */
  public function testEmbeddableRelationPreSaveForNotInUseEmbeddable () {
    /** @var \Drupal\ef\Entity\Embeddable $testEmbeddable */
    $test_embeddable = Embeddable::create([
      'type' => 'test',
      'title' => 'Test title',
    ]);

    $test_embeddable->save();

    $test_embeddable_id = $test_embeddable->id();

    $this->assertNotNull($test_embeddable_id);

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $referer->save();

    $this->assertNotNull($referer->id());

    $relation = EmbeddableRelation::create([
      'embeddable_id' => $test_embeddable->id(),
      'referring_id' => $referer->id(),
      'referring_type' => 'embeddable',
      'referring_field_name' => 'field_some_field_name',
    ]);

    $relation->save();
    $relation_id = $relation->id();
    $this->assertNotNull($relation_id);


    // delete the relation
    $relation->delete();

    $reloaded_relation = EmbeddableRelation::load($relation_id);

    $this->assertNull($reloaded_relation);

    $test_embeddable->delete();

    $reloaded_embeddable = Embeddable::load($test_embeddable_id);

    $this->assertNull($reloaded_embeddable);

  }


  /**
   * @covers ::getReferringEntityId
   * @covers ::getReferringEntityType
   * @covers ::getReferringEntityFieldName
   * @covers ::getEmbeddableId
   */
  public function testEmbeddableRelationAccessors () {
    /** @var \Drupal\ef\Entity\Embeddable $testEmbeddable */
    $testEmbeddable = Embeddable::create([
      'type' => 'test',
      'title' => 'Test title',
    ]);

    $testEmbeddable->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $referer->save();

    $relation = EmbeddableRelation::create([
      'embeddable_id' => $testEmbeddable->id(),
      'referring_id' => $referer->id(),
      'referring_type' => 'embeddable',
      'referring_field_name' => 'field_some_field_name',
    ]);

    $relation->save();

    $this->assertEquals($referer->id(), $relation->getReferringEntityId());
    $this->assertEquals('embeddable', $relation->getReferringEntityType());
    $this->assertEquals('field_some_field_name', $relation->getReferringEntityFieldName());
    $this->assertEquals($testEmbeddable->id(), $relation->getEmbeddableId());
  }

}

