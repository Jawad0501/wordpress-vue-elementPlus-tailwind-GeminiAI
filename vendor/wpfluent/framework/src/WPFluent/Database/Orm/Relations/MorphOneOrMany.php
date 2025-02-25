<?php

namespace FluentGemini\Framework\Database\Orm\Relations;

use FluentGemini\Framework\Database\Orm\Model;
use FluentGemini\Framework\Database\Orm\Builder;

abstract class MorphOneOrMany extends HasOneOrMany
{
    /**
     * The foreign key type for the relationship.
     *
     * @var string
     */
    protected $morphType;

    /**
     * The class name of the parent model.
     *
     * @var string
     */
    protected $morphClass;

    /**
     * Create a new morph one or many relationship instance.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Model  $parent
     * @param  string  $type
     * @param  string  $id
     * @param  string  $localKey
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $type, $id, $localKey)
    {
        $this->morphType = $type;

        $this->morphClass = $parent->getMorphClass();

        parent::__construct($query, $parent, $id, $localKey);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            parent::addConstraints();

            $this->query->where($this->morphType, $this->morphClass);
        }
    }

    /**
     * Get the relationship query.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $parent
     * @param  array|mixed  $columns
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    public function getRelationQuery(Builder $query, Builder $parent, $columns = ['*'])
    {
        $query = parent::getRelationQuery($query, $parent, $columns);

        return $query->where($this->morphType, $this->morphClass);
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        parent::addEagerConstraints($models);

        $this->query->where($this->morphType, $this->morphClass);
    }

    /**
     * Attach a model instance to the parent model.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model  $model
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function save(Model $model)
    {
        $model->setAttribute($this->getPlainMorphType(), $this->morphClass);

        return parent::save($model);
    }

    /**
     * Find a related model by its primary key or return new instance of the related model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \FluentGemini\Framework\Support\Collection|\FluentGemini\Framework\Database\Orm\Model
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();

            // When saving a polymorphic relationship, we need to set not only the foreign
            // key, but also the foreign key type, which is typically the class name of
            // the parent model. This makes the polymorphic item unique in the table.
            $this->setForeignAttributesForCreate($instance);
        }

        return $instance;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);

            // When saving a polymorphic relationship, we need to set not only the foreign
            // key, but also the foreign key type, which is typically the class name of
            // the parent model. This makes the polymorphic item unique in the table.
            $this->setForeignAttributesForCreate($instance);
        }

        return $instance;
    }

    /**
     * Get the first related record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function firstOrCreate(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->create($attributes);
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = $this->firstOrNew($attributes);

        $instance->fill($values);

        $instance->save();

        return $instance;
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function create(array $attributes)
    {
        $instance = $this->related->newInstance($attributes);

        // When saving a polymorphic relationship, we need to set not only the foreign
        // key, but also the foreign key type, which is typically the class name of
        // the parent model. This makes the polymorphic item unique in the table.
        $this->setForeignAttributesForCreate($instance);

        $instance->save();

        return $instance;
    }

    /**
     * Set the foreign ID and type for creating a related model.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model  $model
     * @return void
     */
    protected function setForeignAttributesForCreate(Model $model)
    {
        $model->{$this->getPlainForeignKey()} = $this->getParentKey();

        $model->{last(explode('.', $this->morphType))} = $this->morphClass;
    }

    /**
     * Get the foreign key "type" name.
     *
     * @return string
     */
    public function getMorphType()
    {
        return $this->morphType;
    }

    /**
     * Get the plain morph type name without the table.
     *
     * @return string
     */
    public function getPlainMorphType()
    {
        return last(explode('.', $this->morphType));
    }

    /**
     * Get the class name of the parent model.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return $this->morphClass;
    }
}
