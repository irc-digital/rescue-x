<?php


namespace Drupal\ef;


use Drupal\Core\Entity\EntityViewBuilderInterface;

interface EmbeddableViewBuilderInterface extends EntityViewBuilderInterface {
  public function viewEmbeddable(EmbeddableInterface $embeddable, array $options = [], $view_mode = 'full', $langcode = NULL);
}