<?php

namespace DigitalCreative\NovaDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string key
 * @property string dashboard
 * @property string view
 * @property Collection options
 * @property Collection coordinates
 */
class Widget extends Model
{

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'collection',
        'coordinates' => 'collection',
        'user_id' => 'int',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('nova-dashboard.table_name'));
    }

}
