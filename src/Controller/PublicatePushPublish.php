<?php

namespace Drupal\publicate\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Drupal\publicate\Controller
 */
class PublicatePushPublish {

  /**
   * Main placeholder page. May be deleted eventually.
   */
  public function mainPage() {
    return new Response('Welcome.');
  }

}
