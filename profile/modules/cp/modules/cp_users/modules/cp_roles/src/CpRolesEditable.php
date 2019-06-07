<?php

namespace Drupal\cp_roles;

/**
 * Specifies the roles which cannot be edited/deleted by group admins.
 */
final class CpRolesEditable {

  public const NON_CONFIGURABLE = [
    'personal-anonymous',
  ];

  public const NON_EDITABLE = [
    'personal-administrator',
    'personal-member',
    'personal-content_editor',
  ];

}
