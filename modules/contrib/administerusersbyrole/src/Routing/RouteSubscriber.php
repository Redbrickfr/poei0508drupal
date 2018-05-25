<?php

namespace Drupal\administerusersbyrole\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Provide additional access according to our permissions.

    if ($route = $collection->get('entity.user.collection')) {
      $perm = $route->getRequirement('_permission') . '+access users overview';
      $route->setRequirement('_permission', $perm);
    }

    // @todo: Remove depending on outcome of https://www.drupal.org/project/drupal/issues/2854252
    if ($route = $collection->get('user.multiple_cancel_confirm')) {
      $perm = $route->getRequirement('_permission') . '+access users overview';
      $route->setRequirement('_permission', $perm);
    }

    // @todo: Currently in patch for https://www.drupal.org/project/drupal/issues/2854252
    //if ($route = $collection->get('user.admin_create')) {
    //  $perm = $route->getRequirement('_permission') . '+create users';
    //  $route->setRequirement('_permission', $perm);
    //}
  }
}
