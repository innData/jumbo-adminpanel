<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Image\Manipulations;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * @package App\Models
 * @version July 10, 2018, 11:44 am UTC
 *
 * @property \App\Models\Cart[] cart
 * @property string name
 * @property string email
 * @property string password
 * @property string api_token
 * @property string device_token
 */
class DeliverySlot extends Authenticatable 
{

    /**
     * Validation rules
     *
     * @var array
     */
    // public static $rules = [
    //     'user_id' => 'required|string',
    // ];
    public $table = 'delivery_slots';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'slot_timing'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'slot_timing' => 'string'
    ];
  

}
