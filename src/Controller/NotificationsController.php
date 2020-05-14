<?php

namespace Drupal\stanford_notifications\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\stanford_notifications\NotificationInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class NotificationsController for notification ajax calls.
 *
 * @package Drupal\stanford_notifications\Controller
 */
class NotificationsController extends ControllerBase {

  /**
   * Controller callback when a user clears a notification.
   *
   * @param \Drupal\stanford_notifications\NotificationInterface $notification
   *   Notification entity user chose to clear.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax command to remove the notification from the current view.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function clearNotification(NotificationInterface $notification) {
    if ($this->currentUser()->id() != $notification->userId()) {
      throw new AccessDeniedHttpException($this->t('Invalid user'));
    }

    $notification->delete();
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('[data-notification-id="' . $notification->id() . '"]'));
    return $response;
  }

}
