<?php

namespace Drupal\diocesan_directory\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Directory entity.
 *
 * @ingroup diocesan_directory
 *
 * @ContentEntityType(
 *   id = "directory",
 *   label = @Translation("Directory"),
 *   bundle_label = @Translation("Directory type"),
 *   handlers = {
 *     "storage" = "Drupal\diocesan_directory\DefaultEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\diocesan_directory\DefaultEntityListBuilder",
 *     "views_data" = "Drupal\diocesan_directory\Entity\DefaultEntityViewsData",
 *     "translation" = "Drupal\diocesan_directory\DefaultEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\diocesan_directory\Form\DefaultEntityForm",
 *       "add" = "Drupal\diocesan_directory\Form\DefaultEntityForm",
 *       "edit" = "Drupal\diocesan_directory\Form\DefaultEntityForm",
 *       "delete" = "Drupal\diocesan_directory\Form\DefaultEntityDeleteForm",
 *     },
 *     "access" = "Drupal\diocesan_directory\DefaultEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\diocesan_directory\DefaultEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "directory",
 *   data_table = "directory_field_data",
 *   revision_table = "directory_revision",
 *   revision_data_table = "directory_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer directory entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/directory/{directory}",
 *     "add-page" = "/admin/content/directory/add",
 *     "add-form" = "/admin/content/directory/add/{directory_type}",
 *     "edit-form" = "/admin/content/directory/{directory}/edit",
 *     "delete-form" = "/admin/content/directory/{directory}/delete",
 *     "version-history" = "/admin/content/directory/{directory}/revisions",
 *     "revision" = "/admin/content/directory/{directory}/revisions/{directory_revision}/view",
 *     "revision_revert" = "/admin/content/directory/{directory}/revisions/{directory_revision}/revert",
 *     "translation_revert" = "/admin/content/directory/{directory}/revisions/{directory_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/content/directory/{directory}/revisions/{directory_revision}/delete",
 *     "collection" = "/admin/content/directory",
 *   },
 *   bundle_entity_type = "directory_type",
 *   field_ui_base_route = "entity.directory_type.edit_form"
 * )
 */
class DefaultEntity extends RevisionableContentEntityBase implements DefaultEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the directory owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Directory entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Directory entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Directory is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
