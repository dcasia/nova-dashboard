<?php

namespace DigitalCreative\NovaBi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property string key
 * @property string dashboard
 * @property Collection options
 * @property Collection coordinates
 */
class WidgetModel extends Model
{

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'collection',
        'coordinates' => 'collection'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('nova-widgets.table_name'));
    }

}
