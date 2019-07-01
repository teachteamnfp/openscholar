<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OsUserExtrasResource.
 *
 * @RestResource(
 *   id = "user_extras",
 *   label = @Translation("User Extras"),
 *   uri_paths = {
 *     "canonical" = "/api/entity/user/validate",
 *     "create" = "/api/user/validate"
 *   }
 * )
 */
class OsUserExtrasResource extends ResourceBase {

  /**
   * For the HTTP GET method.
   */
  public function post(array $data) {

    $properties = array_keys($data);
    foreach ($properties as $p) {
      switch ($p) {
        case 'name':
        case 'email':
        case 'password':
          return new ResourceResponse($this->validateData($p, $data[$p]));
      }
    }
    throw new NotFoundHttpException();
  }

  /**
   * Validate a specified field.
   */
  protected function validateData($field, $value) {
    $msg = [];
    switch ($field) {
      case 'name':
        if (empty($value)) {
          $msg[] = $this->t('Please provide a username');
        }
        elseif ($user_error = user_validate_name($value)) {
          $msg[] = array_merge($msg, $user_error);
        }
        elseif (user_load_by_name($value)) {
          $msg[] = $this->t('Username %name is taken. Please choose another.', ['%name' => $value]);
        }
        break;

      case 'email':
        /** @var \Egulias\EmailValidator\EmailValidator $emailValidator */
        $emailValidator = \Drupal::service('email.validator');
        if (empty($value)) {
          $msg[] = $this->t('E-mail is required');
        }
        elseif (!$emailValidator->isValid($value)) {
          $msg[] = $emailValidator->getError();
        }
        elseif (user_load_by_mail($value)) {
          $msg[] = $this->t('An account with the address %mail already exists.', ['%mail' => $value]);
        }
        break;

      case 'password':
        if (empty($value)) {
          $msg[] = $this->t('Password is required');
        }
        break;
    }

    return $msg;
  }

}
