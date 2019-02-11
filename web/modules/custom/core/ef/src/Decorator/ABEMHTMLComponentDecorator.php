<?php


namespace Drupal\ef\Decorator;


class ABEMHTMLComponentDecorator extends HTMLComponentDecoratorBase implements HTMLComponentDecorator {
  private $component;

  public function __construct ($component) {
    $this->component = $component;
  }

//  protected function tidy ($class) {
//    $class = str_replace('_', ' ', $class);
//    $class = str_replace('-', ' ', $class);
//    $class = strtolower($class);
//    $class = ucwords($class);
//    $class = lcfirst ($class);
//    $class = str_replace(' ', '', $class);
//    return $class;
//  }

  public function getClass ($element = NULL, $modifier = NULL) {
    if (is_null($element) && is_null($modifier)) {
      $class = sprintf ('c-%s', $this->tidy($this->component));
    } else {
      $ele = (!is_null($element)) ? sprintf('c-%s__%s', $this->tidy($this->component), $this->tidy($element)) : '';
      $mod = (!is_null($modifier)) ? ' -' . $this->tidy($modifier) : '';

      $class = sprintf ('%s%s', $ele, $mod);
    }

    return $class;
  }

}

