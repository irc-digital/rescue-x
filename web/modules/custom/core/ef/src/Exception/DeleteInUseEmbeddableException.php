<?php

namespace Drupal\ef\Exception;

use Drupal\ef\EmbeddableInterface;

class DeleteInUseEmbeddableException extends \RuntimeException  {
  /** @var EmbeddableInterface */
  private $embeddable;

  public function __construct(EmbeddableInterface $embeddable, $message = "", $code = 0, \Throwable $previous = NULL) {
    parent::__construct($message, $code, $previous);

    $this->embeddable = $embeddable;
  }

  public function getEmbeddable () {
    return $this->embeddable;
  }
}