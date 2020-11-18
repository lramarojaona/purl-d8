<?php

namespace Drupal\purl;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\purl\Utility\PurlAwareUnroutedUrlAssembler;
use Drupal\purl\StackMiddleware\PageCache;
use Drupal\purl\Event\RouteNormalizerRequestSubscriber;
use Symfony\Component\DependencyInjection\Reference;

class PurlServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $urlGeneratorDefinition = $container->getDefinition('url_generator');
    $urlGeneratorDefinition->replaceArgument(0, new Reference('purl.url_generator'));

    if ($container->hasDefinition('redirect.route_normalizer_request_subscriber')) {
      $routeNormalizerDefinition = $container->getDefinition('redirect.route_normalizer_request_subscriber');
      $routeNormalizerDefinition->setClass(RouteNormalizerRequestSubscriber::class);
    }

    $assemblerDefinition = $container->getDefinition('unrouted_url_assembler');
    $assemblerDefinition->setClass(PurlAwareUnroutedUrlAssembler::class);
    $assemblerDefinition->addArgument(new Reference('purl.context_helper'));
    $assemblerDefinition->addArgument(new Reference('purl.matched_modifiers'));

    if ($container->hasDefinition('http_middleware.page_cache')) {
      $pageCacheDefinition = $container->getDefinition('http_middleware.page_cache');
      $pageCacheDefinition->setClass(PageCache::class);
    }
  }

}
