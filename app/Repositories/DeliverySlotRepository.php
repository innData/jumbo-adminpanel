<?php

namespace App\Repositories;

use App\Models\DeliverySlot;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class DriverRepository
 * @package App\Repositories
 * @version March 25, 2020, 9:47 am UTC
 *
 * @method Driver findWithoutFail($id, $columns = ['*'])
 * @method Driver find($id, $columns = ['*'])
 * @method Driver first($columns = ['*'])
*/
class DeliverySlotRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'slot_timing'
        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeliverySlot::class;
    }
}
