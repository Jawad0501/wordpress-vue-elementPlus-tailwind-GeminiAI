<?php

namespace FluentGemini\Framework\Database\Orm\Relations;

use FluentGemini\Framework\Database\Orm\Model;
use FluentGemini\Framework\Database\Orm\Builder;

class Pivot extends Model
{
    /**
     * The parent model of the relationship.
     *
     * @var \FluentGemini\Framework\Database\Orm\Model
     */
    protected $parent;

    /**
     * The name of the foreign key column.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The name of the "other key" column.
     *
     * @var string
     */
    protected $otherKey;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Create a new pivot model instance.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model  $parent
     * @param  array   $attributes
     * @param  string  $table
     * @param  bool    $exists
     * @return void
     */
    public function __construct(Model $parent, $attributes, $table, $exists = false)
    {
        parent::__construct();

        // The pivot model is a "dynamic" model since we will set the tables dynamically
        // for the instance. This allows it work for any intermediate tables for the
        // many to many relationship that are defined by this developer's classes.
        $this->setTable($table);

        $this->setConnection($parent->getConnectionName());

        $this->forceFill($attributes);

        $this->syncOriginal();

        // We store off the parent instance so we will access the timestamp column names
        // for the model, since the pivot model timestamps aren't easily configurable
        // from the developer's point of view. We can use the parents to get these.
        $this->parent = $parent;

        $this->exists = $exists;

        $this->timestamps = $this->hasTimestampAttributes();
    }

    /**
     * Create a new pivot model from raw values returned from a query.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model  $parent
     * @param  array   $attributes
     * @param  string  $table
     * @param  bool    $exists
     * @return static
     */
    public static function fromRawAttributes(Model $parent, $attributes, $table, $exists = false)
    {
        $instance = new static($parent, $attributes, $table, $exists);

        $instance->setRawAttributes($attributes, true);

        return $instance;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where($this->foreignKey, $this->getAttribute($this->foreignKey));

        return $query->where($this->otherKey, $this->getAttribute($this->otherKey));
    }

    /**
     * Delete the pivot model record from the database.
     *
     * @return int
     */
    public function delete()
    {
        return $this->getDeleteQuery()->delete();
    }

    /**
     * Get the query builder for a delete operation on the pivot.
     *
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    protected function getDeleteQuery()
    {
        $foreign = $this->getAttribute($this->foreignKey);

        $query = $this->newQuery()->where($this->foreignKey, $foreign);

        return $query->where($this->otherKey, $this->getAttribute($this->otherKey));
    }

    /**
     * Get the foreign key column name.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the "other key" column name.
     *
     * @return string
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }

    /**
     * Set the key names for the pivot model instance.
     *
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @return $this
     */
    public function setPivotKeys($foreignKey, $otherKey)
    {
        $this->foreignKey = $foreignKey;

        $this->otherKey = $otherKey;

        return $this;
    }

    /**
     * Determine if the pivot model has timestamp attributes.
     *
     * @return bool
     */
    public function hasTimestampAttributes()
    {
        return array_key_exists($this->getCreatedAtColumn(), $this->attributes);
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getCreatedAtColumn()
    {
        return $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function getUpdatedAtColumn()
    {
        return $this->parent->getUpdatedAtColumn();
    }
}
