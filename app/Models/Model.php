<?php namespace App\Models;

use App\Exceptions\NonLoadedRelationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * A base Eloquent model.
 *
 * @package App\Models
 * @since   0.0.1
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Return a timestamp as DateTime object.
     *
     * Overridden to set timezone for all Carbon instances returned.
     *
     * @param  mixed $value
     * @return \Carbon\Carbon
     */
    protected function asDateTime($value)
    {
        $parent_result = parent::asDateTime($value)->toDateTimeString();
        $date = new Carbon($parent_result, 'GB');
        return $date->lt(Carbon::now('GB')->subYears(2000)) ? NULL : $date;
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  \DateTime|int  $value
     * @return string
     */
    public function fromDateTime($value)
    {
        $format = $this->getDateFormat();

        $value = $this->asDateTime($value);

        return (is_null($value)) ? $value : $value->format($format);
    }


    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Carbon\Carbon
     */
    public function freshTimestamp()
    {
        return new Carbon(null, 'GB');
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTime $date
     * @return string
     */
    protected function serializeDate(\DateTime $date = null)
    {
        return is_null($date) ? null : parent::serializeDate($date);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param string $method
     *
     * @return mixed
     * @throws LogicException
     * @throws NonLoadedRelationException
     */
    protected function getRelationshipFromMethod($method)
    {
        if(env('STRICT_EAGER_LOADING', false) && !$this->relationLoaded($method))
        {
            throw new NonLoadedRelationException('Strict Eager Loading: "' . $method . '" relation must be loaded');
        }

        $relations = $this->$method();

        if (! $relations instanceof Relation) {
            throw new \LogicException('Relationship method must return an object of type ' .'Illuminate\Database\Eloquent\Relations\Relation');
        }

        return $this->relations[$method] = $relations->getResults();
    }
}