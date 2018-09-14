<?php


namespace Drupal\ef\Decorator;


abstract class HTMLComponentDecoratorBase implements HTMLComponentDecorator {
  protected function tidy ($class) {
    $class = str_replace('_', '-', $class);
    $class = strtolower($class);

    return $class;
  }
}