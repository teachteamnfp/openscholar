<?php

class BlogNodeRestfulBase extends OsNodeRestfulBase {

  public function derp() {
    error_log(print_r($this->getRequest(), 1));
    return parent::access ();
  }
}
