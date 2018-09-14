<?php
// Drupal translate filter
$filter = new Twig_SimpleFilter('trans', function ($string) {
  return $string;
});