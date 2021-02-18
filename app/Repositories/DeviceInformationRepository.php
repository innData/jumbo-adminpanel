<?php

namespace App\Repositories;

use App\Models\DeviceInformation;
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
class DeviceInformationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id',
        'device_type',
        'model',
        'manufacture',
        'os_version',
        'screen_height',
        'screen_width',
        'brand',
        'updated_at'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeviceInformation::class;
    }
}
