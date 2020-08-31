<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

/**
 * Class OrderRepository
 * @package App\Repositories
 */
class OrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        // TODO: Add searchable field
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Order::class;
    }

    /**
     * Get orders in date range
     *
     * @param Date|null $start
     * @param Date|null $end
     *
     * @return array
     */
    public function getItemsByDates($start, $end)
    {
        $query = $this->model->newQuery();

        $query = $query->where(function($query) use ($start, $end){
            if (!empty($end)) {
                $query = $query->where('date', '<=', $end);
                if (!empty($start)) {
                    $query->where('date', '>', $start);
                }
            }
        });
        $query = $query->OrWhere(function ($query) use ($start, $end) {
            if (!empty($end)) {
                $query = $query->where('date_update', '<=', $end);
                if (!empty($start)) {
                    $query->where('date_update', '>', $start);
                }
            }
        });

        return $query;
    }

    /**
     * Get all orders by shipping date
     *
     * @param Carbon $start
     * @param Carbon $end
     * 
     * @return array
     */
    public function findByShippingDate(Carbon $start, Carbon $end)
    {
        return $this->model
            ->with(['maxOrdernum'])
            ->whereHas('deliveries', function ($query) use ($start, $end) {
                $query->where('shipping_date', '>=', $start)->where('shipping_date', '<=', $end);
            })
            ->groupBy('ordernum')
            ->get(['ordernum']);
    }

}
