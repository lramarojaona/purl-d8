<?php

namespace Drupal\purl\Routing;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\purl\Context;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\Routing\RequestContext;

/**
 * @TODO: Consider decorating @url_generator.non_bubbling instead.
 */
class UrlGenerator implements UrlGeneratorInterface {

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var \Drupal\purl\MatchedModifiers
   */
  protected $matchedModifiers;

  /**
   * @var \Drupal\purl\ContextHelper
   */
  private $contextHelper;

  public function __construct(UrlGeneratorInterface $urlGenerator, MatchedModifiers $matchedModifiers, ContextHelper $contextHelper) {
    $this->urlGenerator = $urlGenerator;
    $this->matchedModifiers = $matchedModifiers;
    $this->contextHelper = $contextHelper;
  }

  /**
   * @param \Symfony\Component\Routing\RequestContext $context
   */
  public function setContext(RequestContext $context) {
    $this->urlGenerator->setContext($context);
  }

  /**
   * @param string|\Symfony\Component\Routing\Route $name
   * @param array $parameters
   * @param array $options
   * @param bool $collect_bubbleable_metadata
   * @return \Drupal\Core\GeneratedUrl|string
   */
  public function generateFromRoute($name, $parameters = [], $options = [], $collect_bubbleable_metadata = FALSE) {
    $hostOverride = NULL;
    $originalHost = NULL;

    $action = array_key_exists('purl_context', $options) && $options['purl_context'] == FALSE ?
      Context::EXIT_CONTEXT : Context::ENTER_CONTEXT;

    $this->contextHelper->preGenerate(
      $this->matchedModifiers->createContexts($action),
      $name,
      $parameters,
      $options,
      $collect_bubbleable_metadata
    );

    if (isset($options['host']) && strlen((string) $options['host']) > 0) {
      $hostOverride = $options['host'];
      $originalHost = $this->getContext()->getHost();
      $this->getContext()->setHost($hostOverride);
    }

    $result = $this->urlGenerator->generateFromRoute($name, $parameters, $options, $collect_bubbleable_metadata);

    // Reset the original host in request context.
    if ($hostOverride) {
      $this->getContext()->setHost($originalHost);
    }

    return $result;
  }

  /**
   * Gets the request context.
   *
   * @return \Symfony\Component\Routing\RequestContext The context
   */
  public function getContext() {
    return $this->urlGenerator->getContext();
  }

  /**
   * @param string $name
   * @param array $parameters
   * @param bool|string $referenceType
   * @return string
   */
  public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH) {
    return $this->urlGenerator->generate($name, $parameters, $referenceType);
  }

  /**
   * @param string|\Symfony\Component\Routing\Route $name
   * @param array $parameters
   * @return string
   */
  public function getPathFromRoute($name, $parameters = []) {
    return $this->urlGenerator->getPathFromRoute($name, $parameters);
  }

  /**
   * @param mixed $name
   * @return bool
   */
  public function supports($name) {
    return $this->urlGenerator->supports($name);
  }

  /**
   * @param mixed $name
   * @param array $parameters
   * @return string
   */
  public function getRouteDebugMessage($name, array $parameters = []) {
    return $this->urlGenerator->getRouteDebugMessage($name, $parameters);
  }

}
