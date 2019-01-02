<?php

namespace Drupal\ef\Decorator;

abstract class HTMLClassDecoratorBase implements HTMLClassDecoratorFactoryInterface {
  /** @var array HTMLComponentDecorator */
  private $decoratorCache = [];

  /**
   * @inheritdoc
   */
  public function getDecorator ($component) {
    if (!isset($this->decoratorCache[$component])) {
      $this->decoratorCache[$component] = $this->createDecorator($component);
    }

    return $this->decoratorCache[$component];
  }

  /**
   * @param $component
   * @return array HTMLComponentDecorator
   */
  protected abstract function createDecorator ($component);
}