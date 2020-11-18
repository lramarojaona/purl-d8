<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\Routing\Route;

interface OutboundRouteAlteringInterface {

  public function alterOutboundRoute($routeName, $modifier, Route $route, array &$parameters, BubbleableMetadata $metadata = NULL);

}
