<?php

namespace Drupal\purl\Routing;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Database\Connection;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\State\StateInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpFoundation\Request;

/**
 * A Route Provider front-end for all Drupal-stored routes.
 */
class PurlRouteProvider extends RouteProvider {

  /**
   * Cache ID prefix used to load routes.
   */
  const ROUTE_LOAD_CID_PREFIX = 'route_provider.route_load:';

  /**
   * The context helper.
   *
   * @var \Drupal\purl\ContextHelper
   */
  protected $contextHelper;

  /**
   * The matched modifiers.
   *
   * @var \Drupal\purl\MatchedModifiers
   */
  protected $matchedModifiers;

  /**
   * Constructs a new PurlRouteProvider.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The path processor.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator.
   * @param string $table
   *   (Optional) The table in the database to use for matching.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   (Optional) The language manager.
   * @param \Drupal\purl\ContextHelper $context_helper
   *   The core route provider.
   * @param \Drupal\purl\MatchedModifiers $matched_modifiers
   *   The core route provider.
   */
  public function __construct(Connection $connection, StateInterface $state, CurrentPathStack $current_path, CacheBackendInterface $cache_backend, InboundPathProcessorInterface $path_processor, CacheTagsInvalidatorInterface $cache_tag_invalidator, $table = 'router', LanguageManagerInterface $language_manager = NULL, ContextHelper $context_helper, MatchedModifiers $matched_modifiers) {
    parent::__construct(
      $connection,
      $state,
      $current_path,
      $cache_backend,
      $path_processor,
      $cache_tag_invalidator
    );
    $this->contextHelper = $context_helper;
    $this->matchedModifiers = $matched_modifiers;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteCollectionForRequest(Request $request) {
    // Cache both the system path as well as route parameters and matching
    // routes.
    $matches = $this->matchedModifiers->getMatched();
    $matches = reset($matches);
    if ($matches) {
      $modifier = $matches->getModifier();
      $cid = 'route:' . '/' . $modifier . $request->getPathInfo() . ':' . $request->getQueryString();
    }
    else {
      $cid = 'route:' . $request->getPathInfo() . ':' . $request->getQueryString();
    }

    if ($cached = $this->cache->get($cid)) {
      $this->currentPath->setPath($cached->data['path'], $request);
      $request->query->replace($cached->data['query']);
      return $cached->data['routes'];
    }
    else {
      // Just trim on the right side.
      $path = $request->getPathInfo();
      $path = $path === '/' ? $path : rtrim($request->getPathInfo(), '/');
      $path = $this->pathProcessor->processInbound($path, $request);
      $this->currentPath->setPath($path, $request);
      // Incoming path processors may also set query parameters.
      $query_parameters = $request->query->all();
      $routes = $this->getRoutesByPath(rtrim($path, '/'));
      if (!empty($routes->count())) {
        $cache_value = [
          'path' => $path,
          'query' => $query_parameters,
          'routes' => $routes,
        ];
        $this->cache->set($cid, $cache_value, CacheBackendInterface::CACHE_PERMANENT, ['route_match']);
      }
      return $routes;
    }
  }
}
