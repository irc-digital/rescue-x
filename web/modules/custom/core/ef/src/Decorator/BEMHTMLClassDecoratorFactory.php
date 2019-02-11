<?php

namespace Drupal\ef\Decorator;

class BEMHTMLClassDecoratorFactory extends HTMLClassDecoratorBase {
  public function createDecorator ($component) {
    return new BEMHTMLComponentDecorator($component);
  }
}