<?php

namespace Drupal\stanford_notifications\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\stanford_notifications\NotificationInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\stanford_notifications\NotificationService;

/**
 * Class NotificationsController for notification ajax calls.
 *
 * @package Drupal\stanford_notifications\Controller
 */
class NotificationsController extends ControllerBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The notification service.
   *
   * @var \Drupal\stanford_notifications\NotificationService
   */
  protected $notificationService;

  /**
   * {@inheritDoc}
   */
  public function __construct(RendererInterface $renderer, NotificationService $notificationService) {
    $this->renderer = $renderer;
    $this->notificationService = $notificationService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('notification_service')
    );
  }

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

    // A CSS selector for the elements to which the data will be attached.
    $selector = '#toolbar-item-notifications';
    $data = $this->notificationService->toolbar();
    unset($data['notifications']['tray']);
    $data['notifications']['tab']['#attributes']['class'][] = 'is-active';
    $data['notifications']['tab']['#attributes']['id'] = 'toolbar-item-notifications';
    $tab = $this->renderer->render($data);
    $response->addCommand(new ReplaceCommand($selector, $tab));

    return $response;
  }

}
