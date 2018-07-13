<?php

namespace Drupal\purl\Event;

use Drupal\Core\Routing\RequestHelper;
use Drupal\redirect\EventSubscriber\RouteNormalizerRequestSubscriber as RedirectRouteNormalizerRequestSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Normalizes GET requests performing a redirect if required.
 *
 * The normalization can be disabled by setting the "_disable_route_normalizer"
 * request parameter to TRUE. However, this should be done before
 * onKernelRequestRedirect() method is executed.
 *
 * Override RedirectRouteNormalizerRequestSubscriber to compare the original
 * uri instead of the request uri that has been altered.
 */
class RouteNormalizerRequestSubscriber extends RedirectRouteNormalizerRequestSubscriber {

  /**
   * Performs a redirect if the URL changes in routing.
   *
   * The redirect happens if a URL constructed from the current route is
   * different from the requested one. Examples:
   * - Language negotiation system detected a language to use, and that language
   *   has a path prefix: perform a redirect to the language prefixed URL.
   * - A route that's set as the front page is requested: redirect to the front
   *   page.
   * - Requested path has an alias: redirect to alias.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {

    if (!$this->config->get('route_normalizer_enabled') || !$event->isMasterRequest()) {
      return;
    }

    $request = $event->getRequest();
    if ($request->attributes->get('_disable_route_normalizer')) {
      return;
    }

    if ($this->redirectChecker->canRedirect($request)) {
      // The "<current>" placeholder can be used for all routes except the front
      // page because it's not a real route.
      $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';

      // Don't pass in the query here using $request->query->all()
      // since that can potentially modify the query parameters.
      $options = ['absolute' => TRUE];
      $redirect_uri = $this->urlGenerator->generateFromRoute($route_name, [], $options);

      // Strip off query parameters added by the route such as a CSRF token.
      if (strpos($redirect_uri, '?') !== FALSE) {
        $redirect_uri  = strtok($redirect_uri, '?');
      }

      // Append back the request query string from $_SERVER.
      $query_string = $request->server->get('QUERY_STRING');
      if ($query_string) {
        $redirect_uri .= '?' . $query_string;
      }

      // Remove /index.php from redirect uri the hard way.
      if (!RequestHelper::isCleanUrl($request)) {
        // This needs to be fixed differently.
        $redirect_uri = str_replace('/index.php', '', $redirect_uri);
      }

      $uri = $request->attributes->has('original_uri') ? $request->attributes->get('original_uri') : $request->getRequestUri();
      $original_uri = $request->getSchemeAndHttpHost() . $uri;
      $original_uri = urldecode($original_uri);
      $redirect_uri = urldecode($redirect_uri);
      if ($redirect_uri != $original_uri) {
        $response = new TrustedRedirectResponse($redirect_uri, $this->config->get('default_status_code'));
        $response->headers->set('X-Drupal-Route-Normalizer', 1);
        $event->setResponse($response);
        // Disable page cache for redirects as that results in unpredictable
        // behavior, e.g. when a trailing ? without query parameters is
        // involved.
        // @todo Remove when https://www.drupal.org/node/2761639 is fixed in
        //   Drupal core.
        \Drupal::service('page_cache_kill_switch')->trigger();
      }
    }
  }

}
