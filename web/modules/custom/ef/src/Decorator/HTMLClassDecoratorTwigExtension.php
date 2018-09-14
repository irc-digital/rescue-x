<?php

namespace Drupal\ef\Decorator;

use Twig_SimpleFilter;
use Twig_Extension;

class HTMLClassDecoratorTwigExtension extends Twig_Extension {
  /** @var HTMLClassDecoratorFactoryInterface  */
  private $classDecoratorFactoryInterface;

  function __construct(HTMLClassDecoratorFactoryInterface $classDecoratorFactoryInterface) {
    $this->classDecoratorFactoryInterface = $classDecoratorFactoryInterface;
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new Twig_SimpleFilter('class', array($this, 'convertToStandardizedClass')),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'ef.html_class_decorator.twig_extension';
  }

  public function convertToStandardizedClass(array $string_array) {
    if (sizeof($string_array) == 0) {
      return 'incorrect class conversation syntax';
    }

    $component = $string_array[0];
    $element = isset($string_array[1]) ? $string_array[1] : NULL;
    $modifier = isset($string_array[2]) ? $string_array[2] : NULL;

    /** @var HTMLComponentDecorator $decorator */
    $decorator = $this->classDecoratorFactoryInterface->getDecorator($component);

    return $decorator->getClass($element, $modifier);
  }
}