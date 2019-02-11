<?php


namespace Drupal\ef;


interface EmbeddableRelationInterface {
  public function getEmbeddableId ();

  public function getReferringEntityId ();

  public function getReferringEntityType ();

  public function getReferringEntityFieldName ();
}