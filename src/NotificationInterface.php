<?php

namespace Drupal\stanford_notifications;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Notification entity type interface.
 *
 * @package Drupal\stanford_notifications
 */
interface NotificationInterface extends ContentEntityInterface {

  /**
   * Get the notification message.
   *
   * @return string
   */
  public function message();

  /**
   * Get the user ID that the notification is for.
   *
   * @return int
   */
  public function userId();

  /**
   * Get the status of the notification.
   *
   * @return string
   */
  public function status();

}
