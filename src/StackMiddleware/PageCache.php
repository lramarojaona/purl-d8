<?php

namespace Drupal\purl\StackMiddleware;

use Drupal\page_cache\StackMiddleware\PageCache as CorePageCache;
use Symfony\Component\HttpFoundation\Request;

/**
 * Executes the page caching before the main kernel takes over the request.
 */
class PageCache extends CorePageCache {

  /**
   * {@inheritdoc}
   *
   * Purl removes the path prefixes and then re-initializes the request. As a
   * result the uri is the same for all requests with a context. By using the
   * original uri, page cache creates an entry per context instead.
   */
  protected function getCacheId(Request $request) {
    $uri = $request->attributes->has('original_uri') ? $request->attributes->get('original_uri') : $request->getRequestUri();
    $cid_parts = [
      $request->getSchemeAndHttpHost() . $uri,
      $request->getRequestFormat(),
    ];
    return implode(':', $cid_parts);
  }

}
