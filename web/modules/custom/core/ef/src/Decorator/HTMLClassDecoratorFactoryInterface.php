<?php

namespace Drupal\ef\Decorator;

interface HTMLClassDecoratorFactoryInterface {
  /**
   * @param $component string component name
   * @return HTMLComponentDecorator
   */
  public function getDecorator ($component);

}