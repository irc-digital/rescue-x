<?php

namespace Drupal\ef\Decorator;

class ABEMHTMLClassDecoratorFactory extends HTMLClassDecoratorBase {
  public function createDecorator ($component) {
    return new ABEMHTMLComponentDecorator($component);
  }
}