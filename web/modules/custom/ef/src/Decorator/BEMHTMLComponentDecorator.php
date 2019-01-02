<?php


namespace Drupal\ef\Decorator;


class BEMHTMLComponentDecorator extends HTMLComponentDecoratorBase implements HTMLComponentDecorator {
  private $component;

  public function __construct ($component) {
    $this->component = $component;
  }

  public function getClass ($element = NULL, $modifier = NULL) {
    $ele = (!is_null($element)) ? '__' . $this->tidy($element) : '';
    $mod = (!is_null($modifier)) ? '--' . $this->tidy($modifier) : '';

    $class = sprintf ('c-%s%s%s', $this->tidy($this->component), $ele, $mod);

    return $class;
  }

}