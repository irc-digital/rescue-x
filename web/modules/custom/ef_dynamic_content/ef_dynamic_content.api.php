<?php

/**
 * A hook to be used to add a content type into the type filter for ef_dynamic_content
 *
 * This basically allows us to avoid ef_dynamic_content assuming that it knows
 * all that types that are considered 'news'. This can avoid a complex dependency
 * graph that ends up making everything dependent on everything else.
 *
 * @return string
 */
function hook_dynamic_content_view_filter_bundle () {
  return 'article';
}

/**
 * Allows a module to tell the dynamic content view logic which field it uses
 * to join to news-ie type stuff. i.e. the country and article types might be
 * linked together with a field called field_countries.
 *
 * Note: we only support a single relationship between the news-type content
 * and the category.
 *
 * Bundle is the name of the content bundle e.g. topic ... country ... person
 *
 * @return string
 */
function hook_dynamic_content_view_argument_field_for_BUNDLE () {
  return 'field_topics';
}