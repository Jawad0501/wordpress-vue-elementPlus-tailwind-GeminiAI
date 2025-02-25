<?php

namespace FluentGemini\Framework\Database\Orm\Relations;

use FluentGemini\Framework\Database\Orm\Model;
use FluentGemini\Framework\Database\Orm\Builder;
use FluentGemini\Framework\Database\Orm\Collection;
use FluentGemini\Framework\Database\Query\Expression;

class BelongsTo extends Relation
{
    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model.
     *
     * @var string
     */
    protected $otherKey;

    /**
     * The name of the relationship.
     *
     * @var string
     */
    protected $relation;

    /**
     * The count of self joins.
     *
     * @var int
     */
    protected static $selfJoinCount = 0;

    /**
     * Create a new belongs to relationship instance.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $relation
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $otherKey, $relation)
    {
        $this->otherKey = $otherKey;
        $this->relation = $relation;
        $this->foreignKey = $foreignKey;

        parent::__construct($query, $parent);
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->first();
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $table = $this->related->getTable();

            $this->query->where($table.'.'.$this->otherKey, '=', $this->parent->{$this->foreignKey});
        }
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $parent
     * @param  array|mixed  $columns
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    public function getRelationQuery(Builder $query, Builder $parent, $columns = ['*'])
    {
        if ($parent->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationQueryForSelfRelation($query, $parent, $columns);
        }

        $query->select($columns);

        $otherKey = $this->wrap($query->getModel()->getTable().'.'.$this->otherKey);

        return $query->where($this->getQualifiedForeignKey(), '=', new Expression($otherKey));
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $parent
     * @param  array|mixed  $columns
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    public function getRelationQueryForSelfRelation(Builder $query, Builder $parent, $columns = ['*'])
    {
        $query->select($columns);

        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        $key = $this->wrap($this->getQualifiedForeignKey());

        return $query->where($hash.'.'.$query->getModel()->getKeyName(), '=', new Expression($key));
    }

    /**
     * Get a relationship join table hash.
     *
     * @return string
     */
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        // We'll grab the primary key name of the related models since it could be set to
        // a non-standard name and not "id". We will then construct the constraint for
        // our eagerly loading query so it returns the proper models from execution.
        $key = $this->related->getTable().'.'.$this->otherKey;

        $this->query->whereIn($key, $this->getEagerModelKeys($models));
    }

    /**
     * Gather the keys from an array of related models.
     *
     * @param  array  $models
     * @return array
     */
    protected function getEagerModelKeys(array $models)
    {
        $keys = [];

        // First we need to gather all of the keys from the parent models so we know what
        // to query for via the eager loading query. We will add them to an array then
        // execute a "where in" statement to gather up all of those related records.
        foreach ($models as $model) {
            if (! is_null($value = $model->{$this->foreignKey})) {
                $keys[] = $value;
            }
        }

        // If there are no keys that were not null we will just return an array with either
        // null or 0 in (depending on if incrementing keys are in use) so the query wont
        // fail plus returns zero results, which should be what the developer expects.
        if (count($keys) === 0) {
            return [$this->related->getIncrementing() ? 0 : null];
        }

        return array_values(array_unique($keys));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \FluentGemini\Framework\Database\Orm\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;

        $other = $this->otherKey;

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->getAttribute($other)] = $result;
        }

        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
        foreach ($models as $model) {
            if (isset($dictionary[$model->$foreign])) {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            }
        }

        return $models;
    }

    /**
     * Associate the model instance to the given parent.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model|int  $model
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function associate($model)
    {
        $otherKey = ($model instanceof Model ? $model->getAttribute($this->otherKey) : $model);

        $this->parent->setAttribute($this->foreignKey, $otherKey);

        if ($model instanceof Model) {
            $this->parent->setRelation($this->relation, $model);
        }

        return $this->parent;
    }

    /**
     * Dissociate previously associated model from the given parent.
     *
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);

        return $this->parent->setRelation($this->relation, null);
    }

    /**
     * Update the parent model on the relationship.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function update(array $attributes)
    {
        $instance = $this->getResults();

        return $instance->fill($attributes)->save();
    }

    /**
     * Get the foreign key of the relationship.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the fully qualified foreign key of the relationship.
     *
     * @return string
     */
    public function getQualifiedForeignKey()
    {
        return $this->parent->getTable().'.'.$this->foreignKey;
    }

    /**
     * Get the associated key of the relationship.
     *
     * @return string
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }

    /**
     * Get the fully qualified associated key of the relationship.
     *
     * @return string
     */
    public function getQualifiedOtherKeyName()
    {
        return $this->related->getTable().'.'.$this->otherKey;
    }
}
