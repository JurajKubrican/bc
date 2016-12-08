<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class User extends \NeoEloquent  implements Authenticatable
{
use AuthenticableTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
    * one user to many Places
    */
    public function follows()
    {
        return $this->hasMany('App\Place', 'FOLLOWS');
    }

    /**
    * one user to one Place - HOME
    */
    public function home()
    {
        return $this->hasOne('App\Place', 'HOME');
    }

    public function setPassword($pass){
      $this->password = bcrypt($pass);
      $this->save;
    }
}
