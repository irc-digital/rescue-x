<?php

namespace Drupal\ef_test\Plugin\EmbeddableUsage;

use Drupal\Core\Entity\EntityInterface;
use Drupal\ef\EmbeddableUsageInterface;
use Drupal\ef\Entity\Embeddable;
use Drupal\ef\Plugin\Annotation\EmbeddableUsage;

/**
 * Class TestEmbeddableUsage
 * @package Drupal\ef_test\Plugin\EmbeddableUsage
 *
 * @EmbeddableUsage(
 *  id = "test_embeddable_usage"
 * )
 */
class TestEmbeddableUsage implements EmbeddableUsageInterface {
  /** @var EntityInterface */
  private static $testEmbeddable1;

  /** @var EntityInterface */
  private static $testEmbeddable2;

  /** @var EntityInterface */
  private static $testEmbeddable3;

  /**
   * @inheritdoc
   */
  public function getUsedEmbeddableEntities(EntityInterface $entity) {
    if ($entity->getEntityTypeId() == 'embeddable' && $entity->bundle() == 'referer') {

      if (is_null(self::$testEmbeddable1)) {
        // if this is the first time this is called lets pretend we have three
        // embeddables associated with the entity
        self::$testEmbeddable1 = Embeddable::create([
          'type' => 'test',
          'title' => 'Test one',
        ]);

        self::$testEmbeddable1->save();

        self::$testEmbeddable2 = Embeddable::create([
          'type' => 'test',
          'title' => 'Test two',
        ]);

        self::$testEmbeddable2->save();

        self::$testEmbeddable3 = Embeddable::create([
          'type' => 'test',
          'title' => 'Test three',
        ]);

        self::$testEmbeddable3->save();

        return [
          'field_test' => [
            self::$testEmbeddable1->id(),
            self::$testEmbeddable2->id(),
            self::$testEmbeddable3->id(),
          ],
        ];
      } else {
        // on subsequent calls lets pretend one has been unassociated
        return [
          'field_test' => [
            self::$testEmbeddable1->id(),
            self::$testEmbeddable3->id(),
          ],
        ];
      }
    }

    return [];
  }

}