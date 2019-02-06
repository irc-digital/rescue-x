<?php


namespace Drupal\Tests\ef;

use Drupal\ef\Entity\Embeddable;
use Drupal\ef_test\EmbeddableReferencesTraitImplementation;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ef\EmbeddableReferencesTrait;

/**
 * Class EmbeddableReferencesTraitTest
 * @package Drupal\Tests\ef
 *
 * The testing of the trait is a bit tricky, as the methods on it are
 * protected
 *
 * @group ef
 */
class EmbeddableReferencesTraitTest extends KernelTestBase {
  public static $modules = ['system', 'field', 'image', 'media', 'file', 'text', 'language', 'content_translation', 'user', 'filter', 'crop', 'image_widget_crop', 'node', 'ds', 'paragraphs', 'ef', 'ef_test'];

  public function setUp() {
    parent::setUp();

    $this->installConfig(['system', 'language', 'field', 'filter', 'text', 'node', 'ef', 'ef_test']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
  }


  public function testGetAllEntityReferenceEmbeddableItemFields () {
    /** @var EmbeddableReferencesTrait $embeddableReferencesTrait */
    $embeddableReferencesTrait = $this->getMockForTrait('Drupal\ef\EmbeddableReferencesTrait');

    /** @var \ReflectionMethod $getAllEntityReferenceEmbeddableItemFields */
    $getAllEntityReferenceEmbeddableItemFields = $this->getNonPublicMethod ($embeddableReferencesTrait, 'getAllEntityReferenceEmbeddableItemFields');

    $fields_with_embeddable_references = $getAllEntityReferenceEmbeddableItemFields->invoke($embeddableReferencesTrait, 'embeddable', 'referer');

    $this->assertCount(3, $fields_with_embeddable_references);

    $fields_with_embeddable_references = $getAllEntityReferenceEmbeddableItemFields->invoke($embeddableReferencesTrait, 'embeddable', 'test');

    $this->assertCount(0, $fields_with_embeddable_references);
  }

  public function testGetAllEntityReferenceEmbeddableItemFieldsOnEntity () {
    /** @var EmbeddableReferencesTrait $embeddableReferencesTrait */
    $embeddableReferencesTrait = $this->getMockForTrait('Drupal\ef\EmbeddableReferencesTrait');

    /** @var \ReflectionMethod $getAllEntityReferenceEmbeddableItemFields */
    $getAllEntityReferenceEmbeddableItemFieldsOnEntity = $this->getNonPublicMethod ($embeddableReferencesTrait, 'getAllEntityReferenceEmbeddableItemFieldsOnEntity');

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $fields_with_embeddable_references = $getAllEntityReferenceEmbeddableItemFieldsOnEntity->invoke($embeddableReferencesTrait, $referer);

    $this->assertCount(3, $fields_with_embeddable_references);

    $test = Embeddable::create([
      'type' => 'test',
      'title' => 'Test',
    ]);

    $fields_with_embeddable_references = $getAllEntityReferenceEmbeddableItemFieldsOnEntity->invoke($embeddableReferencesTrait, $test);

    $this->assertCount(0, $fields_with_embeddable_references);
  }

  public function testGetAllDependentEntityReferenceEmbeddableItemFields () {
    /** @var EmbeddableReferencesTrait $embeddableReferencesTrait */
    $embeddableReferencesTrait = $this->getMockForTrait('Drupal\ef\EmbeddableReferencesTrait');

    /** @var \ReflectionMethod $getAllEntityReferenceEmbeddableItemFields */
    $getAllDependentEntityReferenceEmbeddableItemFields = $this->getNonPublicMethod ($embeddableReferencesTrait, 'getAllDependentEntityReferenceEmbeddableItemFields');

    $fields_with_embeddable_references = $getAllDependentEntityReferenceEmbeddableItemFields->invoke($embeddableReferencesTrait, 'embeddable', 'referer');

    $this->assertCount(1, $fields_with_embeddable_references);

    $fields_with_embeddable_references = $getAllDependentEntityReferenceEmbeddableItemFields->invoke($embeddableReferencesTrait, 'embeddable', 'test');

    $this->assertCount(0, $fields_with_embeddable_references);
  }

  public function testGetAllDependentEntityReferenceEmbeddableItemFieldsOnEntity () {
    /** @var EmbeddableReferencesTrait $embeddableReferencesTrait */
    $embeddableReferencesTrait = $this->getMockForTrait('Drupal\ef\EmbeddableReferencesTrait');

    /** @var \ReflectionMethod $getAllEntityReferenceEmbeddableItemFields */
    $getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity = $this->getNonPublicMethod ($embeddableReferencesTrait, 'getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity');

    $referer = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test referer',
    ]);

    $fields_with_embeddable_references = $getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity->invoke($embeddableReferencesTrait, $referer);

    $this->assertCount(1, $fields_with_embeddable_references);

    $test = Embeddable::create([
      'type' => 'test',
      'title' => 'Test',
    ]);

    $fields_with_embeddable_references = $getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity->invoke($embeddableReferencesTrait, $test);

    $this->assertCount(0, $fields_with_embeddable_references);
  }

  /**
   * @param $method_name
   * @return \ReflectionMethod
   * @throws \ReflectionException
   */
  protected function getNonPublicMethod ($object, $method_name) {
    $class = new \ReflectionClass($object);
    $method = $class->getMethod($method_name);
    $method->setAccessible(true);
    return $method;
  }
}