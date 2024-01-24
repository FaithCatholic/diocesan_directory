<?php

namespace Drupal\diocesan_directory\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Directory type entity.
 *
 * @ConfigEntityType(
 *   id = "directory_type",
 *   label = @Translation("Directory type"),
 *   handlers = {
 *     "list_builder" = "Drupal\diocesan_directory\DefaultEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\diocesan_directory\Form\DefaultEntityTypeForm",
 *       "edit" = "Drupal\diocesan_directory\Form\DefaultEntityTypeForm",
 *       "delete" = "Drupal\diocesan_directory\Form\DefaultEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\diocesan_directory\DefaultEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "directory_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "directory",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/directory_type/{directory_type}",
 *     "add-form" = "/admin/structure/directory_type/add",
 *     "edit-form" = "/admin/structure/directory_type/{directory_type}/edit",
 *     "delete-form" = "/admin/structure/directory_type/{directory_type}/delete",
 *     "collection" = "/admin/structure/directory_type"
 *   },
 *   config_export= {
 *     "id",
 *     "label",
 *   }
 * )
 */
class DefaultEntityType extends ConfigEntityBundleBase implements DefaultEntityTypeInterface {

  /**
   * The Directory type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Directory type label.
   *
   * @var string
   */
  protected $label;

}
