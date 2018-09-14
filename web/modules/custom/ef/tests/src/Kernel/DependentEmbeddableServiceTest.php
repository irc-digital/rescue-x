<?php

namespace Drupal\Tests\ef;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Entity\Embeddable;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class DependentEmbeddableServiceTest
 *
 * @coversDefaultClass \Drupal\ef\DependentEmbeddableService
 *
 * @package Drupal\Tests\ef
 */
class DependentEmbeddableServiceTest extends KernelTestBase {

  public static $modules = ['field', 'user', 'filter', 'ef', 'ef_test'];

  /**
   * The mocked language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  public function setUp() {
    parent::setUp();

    $this->installConfig(['ef', 'ef_test']);
    $this->installEntitySchema('embeddable');
    $this->installEntitySchema('embeddable_relation');
    $this->installEntitySchema('user');

    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $english = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $english->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $german = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $german->expects($this->any())
      ->method('getId')
      ->willReturn('de');
    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->onConsecutiveCalls($english, $english, $german, $german));

    $this->languageManager->expects($this->any())
      ->method('getLanguages')
      ->willReturn(['en' => $english, 'de' => $german]);

    $this->languageManager->expects($this->any())
      ->method('getDefaultLanguage')
      ->will($this->returnValue($english));

    \Drupal::getContainer()->set('language_manager', $this->languageManager);
  }

  /**
   * @covers ::onInsert
   * @covers ::onPresave
   * @covers ::onUpdate
   */
  public function testCreatingDependentEmbeddable () {
    $parent_embeddable = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test title',
    ]);

    $parent_embeddable->save();

    $this->assertNotNull($parent_embeddable->field_dependent_embeddable_ref);

    /** @var \Drupal\ef\EmbeddableInterface $dependent_embeddable */
    $dependent_embeddable = $parent_embeddable->field_dependent_embeddable_ref->entity;

    $this->assertNotNull($dependent_embeddable);

    $this->assertInstanceOf(EmbeddableInterface::class, $dependent_embeddable);

    $resultant_parent_entity = $dependent_embeddable->getParentEntity();
    $this->assertNotNull($resultant_parent_entity);

    $this->assertEquals($parent_embeddable->getEntityTypeId(), $resultant_parent_entity->getEntityTypeId());
    $this->assertEquals($parent_embeddable->id(), $resultant_parent_entity->id());

    $this->assertEquals('Test for Test title', $dependent_embeddable->label());

    $parent_embeddable->setTitle('Oh yeah!');
    $parent_embeddable->save();
    $this->assertEquals('Test for Oh yeah!', $dependent_embeddable->label());

  }

  public function testPreventDeletingInUseDependentEmbeddable () {
    $parent_embeddable = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test title',
    ]);

    $parent_embeddable->save();

    /** @var \Drupal\ef\EmbeddableInterface $dependent_embeddable */
    $dependent_embeddable = $parent_embeddable->field_dependent_embeddable_ref->entity;

    $this->expectException(EntityStorageException::class);
    $dependent_embeddable->delete();
  }

  /**
   * @covers ::onDelete
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEnsureDependentEmbeddableRemovedWhenParentIsRemoved () {
    $parent_embeddable = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test title',
    ]);

    $parent_embeddable->save();

    $dependent_embeddable_id = $parent_embeddable->field_dependent_embeddable_ref->entity->id();

    $parent_embeddable->delete();

    $dependent_embeddable = Embeddable::load($dependent_embeddable_id);

    $this->assertNull($dependent_embeddable);
  }

  /**
   * @covers ::isDependentEmbeddableType
   */
  public function testIsDependentEmbeddableType () {
    /** @var \Drupal\ef\DependentEmbeddableServiceInterface $dependent_embeddable_service */
    $dependent_embeddable_service = \Drupal::service('ef.dependent_embeddable');

    $this->assertTrue($dependent_embeddable_service->isDependentEmbeddableType('test'));
    $this->assertFalse($dependent_embeddable_service->isDependentEmbeddableType('referer'));
  }

  /**
   * @covers ::onTranslationDelete
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testMultilingual () {
    $english_parent = Embeddable::create([
      'type' => 'referer',
      'title' => 'Test title',
      'language' => 'en',
    ]);

    $english_parent->save();

    $this->assertNotNull($english_parent->field_dependent_embeddable_ref);

    $dependent_embeddable_id = $english_parent->field_dependent_embeddable_ref->entity->id();
    $dependent_embeddable = Embeddable::load($dependent_embeddable_id);
    $this->assertNotNUll($dependent_embeddable);
    $this->assertEquals($english_parent->language()->getId(), $dependent_embeddable->language()->getId());

    /** @var EmbeddableInterface $german_parent_version */
    $german_parent_version = $english_parent->addTranslation('de', ['title' => 'Test title in German']);
    $german_parent_version->save();
    $german_parent_version = $german_parent_version->getTranslation('de');

    $this->assertNotNull($german_parent_version->field_dependent_embeddable_ref);
    $this->assertEquals($dependent_embeddable_id, $german_parent_version->field_dependent_embeddable_ref->entity->id());

    $dependent_embeddable = Embeddable::load($dependent_embeddable_id);
    $this->assertTrue($dependent_embeddable->isDefaultTranslation());
    $this->assertTrue($dependent_embeddable->hasTranslation('de'));
    /** @var EmbeddableInterface $german_embeddable_version */
    $german_embeddable_version = $dependent_embeddable->getTranslation('de');

    $this->assertEquals('Test for Test title in German', $german_embeddable_version->label());
    $this->assertEquals('Test for Test title', $dependent_embeddable->label());

    // deletes
    try {
      $german_embeddable_version->removeTranslation('de');
      $german_embeddable_version->save();
      $this->throwException(new Exception("Able to delete in-use translation"));
    } catch (EntityStorageException $e) {
      // success
    }

    try {
      $dependent_embeddable->delete();
      $this->throwException(new Exception("Able to delete in-use entity"));
    } catch (EntityStorageException $e) {
      // success
    }

    $english_parent_version = $german_parent_version->getTranslation('en');
    $german_parent_version->removeTranslation('de');
    $english_parent_version->save();

    $dependent_embeddable = Embeddable::load($dependent_embeddable_id);

    $this->assertFalse($dependent_embeddable->hasTranslation('de'));

    $english_parent_version->delete();

    $dependent_embeddable = Embeddable::load($dependent_embeddable_id);

    $this->assertNull($dependent_embeddable);
  }
}