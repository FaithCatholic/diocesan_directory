<?php

namespace Drupal\diocesan_directory\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultEntityTypeForm.
 *
 * @package Drupal\diocesan_directory\Form
 */
class DefaultEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $directory_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $directory_type->label(),
      '#description' => $this->t("Label for the Directory type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $directory_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\diocesan_directory\Entity\DefaultEntityType::load',
      ],
      '#disabled' => !$directory_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $directory_type = $this->entity;
    $status = $directory_type->save();

    switch ($status) {
      case SAVED_NEW:
        \Drupal\Core\Messenger\MessengerInterface::addMessage($this->t('Created the %label Directory type.', [
          '%label' => $directory_type->label(),
        ]));
        break;

      default:
        \Drupal\Core\Messenger\MessengerInterface::addMessage($this->t('Saved the %label Directory type.', [
          '%label' => $directory_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($directory_type->toUrl('collection'));
  }

}
