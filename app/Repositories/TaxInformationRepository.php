<?php

namespace App\Repositories;

use App\Models\TaxInformation;
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
class TaxInformationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id',
        'tax_number',
        'tax_owner_name'
        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TaxInformation::class;
    }
}
