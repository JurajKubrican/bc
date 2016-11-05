<?php

namespace App;

class Place extends \NeoEloquent
{
  /**
  * one user to many Places
  */
  public function route()
  {
      return $this->belongsToMany('App\Place', 'ROUTE');
  }
}
