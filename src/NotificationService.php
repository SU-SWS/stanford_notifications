<?php

namespace Drupal\stanford_notifications;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Notification entity helper service.
 *
 * @package Drupal\stanford_notifications
 */
class NotificationService implements NotificationServiceInterface {

  use StringTranslationTrait;

  /**
   * Current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function toolbar() {
    $notification_list = [];
    $cache_tags = ['notifications:' . $this->currentUser->id()];

    foreach ($this->getUserNotifications() as $notification) {
      $cache_tags[] = 'notification:' . $notification->id();
      $clear_link = Link::createFromRoute($this->t('Delete'), 'stanford_notifications.clear', ['notification' => $notification->id()], ['attributes' => ['class' => ['use-ajax']]]);
      $notification_list[] = [
        '#wrapper_attributes' => [
          'data-notification-id' => $notification->id(),
          'class' => [
            'menu-item',
            Html::cleanCssIdentifier($notification->status()),
          ],
        ],
        '#markup' => $notification->message() . $clear_link->toString(),
      ];
    }

    $items['notifications'] = [
      '#type' => 'toolbar_item',
      '#weight' => 999,
      'tab' => [
        '#type' => 'link',
        '#title' => $this->t('Notifications'),
        '#url' => Url::fromRoute('<front>'),
        '#attributes' => [
          'title' => $this->t('Notifications'),
          'class' => ['toolbar-icon', 'toolbar-icon-notifications'],
          'data-notification-count' => count($notification_list),
        ],
      ],
      'tray' => [
        '#theme' => 'item_list',
        '#items' => $notification_list,
        '#attributes' => ['class' => ['notification-list']],
      ],
      '#cache' => [
        'keys' => [$this->currentUser->id()],
        'tags' => $cache_tags,
      ],
      '#attached' => [
        'library' => [
          'stanford_notifications/notifications',
        ],
      ],
    ];
    return $items;
  }

  /**
   * {@inheritDoc}
   */
  public function addNotification($message, array $roles = [], $status = Messenger::TYPE_STATUS) {
    $user_query = $this->entityTypeManager->getStorage('user')->getQuery();
    if ($roles) {
      $user_query->condition('roles', $roles, 'IN');
    }

    $notification_storage = $this->entityTypeManager->getStorage('notification');
    $tags_to_invalidate = [];
    foreach ($user_query->execute() as $user_id) {
      $notification_storage->create([
        'message' => $message,
        'uid' => $user_id,
        'status' => $status,
      ])->save();
      $tags_to_invalidate[] = "notifications:$user_id";
    }
    Cache::invalidateTags(array_unique($tags_to_invalidate));
  }

  /**
   * {@inheritDoc}
   */
  public function getUserNotifications(AccountProxyInterface $account = NULL) {
    if (!$account) {
      $account = $this->currentUser;
    }
    return $this->entityTypeManager->getStorage('notification')
      ->loadByProperties(['uid' => $account->id()]);
  }

  /**
   * {@inheritDoc}
   */
  public function clearUserNotifications(AccountProxyInterface $account = NULL) {
    foreach ($this->getUserNotifications($account) as $notification) {
      $notification->delete();
    }
  }

}
