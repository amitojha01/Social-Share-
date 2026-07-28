<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class UserSocialAccount extends Model
{

    protected $fillable = [
        'user_id','provider','provider_user_id','access_token','page_id','page_name', 'page_token', 'updated_at','created_at', 'instagram_account_id','username','token_expires_at'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    

}
