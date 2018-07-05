<?php

class BlogNodeRestfulBase extends OsNodeRestfulBase {

  public function access() {
    echo print_r($this->getRequest(), 1);
    return parent::access ();
  }
}
