<?php

namespace Drupal\stanford_notifications;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxyInterface;

interface NotificationServiceInterface {

  /**
   * Callable for hook_toolbar().
   *
   * @return array
   */
  public function toolbar();

  /**
   * Add a notification for various users.
   *
   * @param string $message
   *   Message to present to the user.
   * @param array $roles
   *   Optionally specify which roles to set a notifiction for.
   * @param string $status
   *   Severity of the notification.
   */
  public function addNotification($message, $roles = [], $status = Messenger::TYPE_STATUS);

  /**
   * Get all notification entities for a given account or the current user.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface|null $account
   *   Optionally get notifications for this account.
   *
   * @return \Drupal\stanford_notifications\Entity\Notification[]
   *   Array of notifications for the user.
   */
  public function getUserNotifications(AccountProxyInterface $account = NULL);

  /**
   * Clear out all notifications for the user.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   User account to clear.
   */
  public function clearUserNotifications(AccountProxyInterface $account);

}
