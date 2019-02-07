<?php

namespace Drupal\Tests\ef_wysiwyg_embed\Kernel;

use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Entity\EmbeddableRelation;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class WysiwygEmbedUsagePluginTest
 *
 * @coversDefaultClass \Drupal\ef_wysiwyg_embed\Plugin\EmbeddableUsage\WysiwygUsage
 * @package Drupal\Tests\ef_wysiwyg_embed\Kernel
 */
class WysiwygEmbedUsagePluginTest extends KernelTestBase  {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'embed', 'entity_embed', 'ef', 'ef_test', 'ef_wysiwyg_embed', 'ef_wysiwyg_embed_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['field', 'filter', 'language', 'text', 'embed', 'entity_embed', 'ef', 'ef_test', 'ef_wysiwyg_embed_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
  }

  /**
   * @covers ::getUsedEmbeddableEntities
   * @covers ::extractEmbeddableIdsFromText
   * @covers ::getAllRichTextFields
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWysiwygEmbedUsagePluginSingle () {
    $reference_1 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);

    $reference_1->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test 1',
      'field_rich_text_single' => [
        'value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_1->uuid() . '"></drupal-entity><p>Some more text.</p>',
      ],
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_single', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(1, $relations);

    $referer->field_rich_text_single->value = '<p>Some text, no embeddable</p>';
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_single', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(0, $relations);

  }

  /**
   * @covers ::getUsedEmbeddableEntities
   * @covers ::extractEmbeddableIdsFromText
   * @covers ::getAllRichTextFields
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWysiwygEmbedUsagePluginMultiple () {
    $reference_1 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);

    $reference_1->save();

    $reference_2 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 2',
    ]);

    $reference_2->save();

    $reference_3 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 3',
    ]);

    $reference_3->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test 1',
      'field_rich_text_multiple' => [
        ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_1->uuid() . '"></drupal-entity><p>Some more text.</p>'],
        ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_2->uuid() . '"></drupal-entity><p>Some more text.</p>'],
        ['value' => '<p>Some text - no embeddable.</p>'],
        ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_3->uuid() . '"></drupal-entity><p>Some more text.</p>'],
        ['value' => '<p>Repeated embeddable.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_3->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      ],
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_multiple', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

    $referer->field_rich_text_multiple = [
      ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_2->uuid() . '"></drupal-entity><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_1->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_3->uuid() . '"></drupal-entity><p>Some more text.</p>'],
    ];

    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_multiple', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

    $referer->field_rich_text_multiple = NULL;
    $referer->save();
    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_multiple', '=')
      ->execute();
    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(0, $relations);
  }

  /**
   * @covers ::getUsedEmbeddableEntities
   * @covers ::extractEmbeddableIdsFromText
   * @covers ::getAllRichTextFields
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWysiwygEmbedUsagePluginSingleAndMultiple () {
    $reference_1 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 1',
    ]);

    $reference_1->save();

    $reference_2 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 2',
    ]);

    $reference_2->save();

    $reference_3 = Embeddable::create([
      'type' => 'test',
      'title' => 'Test 3',
    ]);

    $reference_3->save();

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test 1',
      'field_rich_text_single' => ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_1->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      'field_rich_text_multiple' => [
        ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_2->uuid() . '"></drupal-entity><p>Some more text.</p>'],
        ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_3->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      ],
    ]);

    $referer->save();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $relation_storage */
    $relation_storage = \Drupal::service('entity_type.manager')->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', ['field_rich_text_multiple', 'field_rich_text_single'], 'IN')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

    $referer->field_rich_text_multiple = [
      ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_2->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_3->uuid() . '"></drupal-entity><p>Some more text.</p>'],
      ['value' => '<p>Some text.</p><drupal-entity data-embed-button="embeddable" data-entity-uuid="' . $reference_1->uuid() . '"></drupal-entity><p>Some more text.</p>'],
    ];
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', ['field_rich_text_multiple', 'field_rich_text_single'], 'IN')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(4, $relations);

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', 'field_rich_text_multiple', '=')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

    $referer->field_rich_text_single = NULL;
    $referer->save();

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_field_name', ['field_rich_text_multiple', 'field_rich_text_single'], 'IN')
      ->execute();

    $relations = EmbeddableRelation::loadMultiple($existing_relation_ids);

    $this->assertCount(3, $relations);

  }
}