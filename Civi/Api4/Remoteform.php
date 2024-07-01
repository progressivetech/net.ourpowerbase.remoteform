<?php
namespace Civi\Api4;


/**
 * Remoteform entity.
 *
 * Provided by the remoteform extension.
 *
 * @package Civi\Api4
 */
class Remoteform extends Generic\AbstractEntity {

  public static function getFields() {
    return new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function() {
      return [ ];
    });
  }
}

