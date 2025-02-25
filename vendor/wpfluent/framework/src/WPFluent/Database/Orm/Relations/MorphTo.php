<?php

namespace FluentGemini\Framework\Database\Orm\Relations;

use BadMethodCallException;
use FluentGemini\Framework\Database\Orm\Model;
use FluentGemini\Framework\Database\Orm\Builder;
use FluentGemini\Framework\Database\Orm\Collection;

class MorphTo extends BelongsTo
{
    /**
     * The type of the polymorphic relation.
     *
     * @var string
     */
    protected $morphType;

    /**
     * The models whose relations are being eager loaded.
     *
     * @var \FluentGemini\Framework\Database\Orm\Collection
     */
    protected $models;

    /**
     * All of the models keyed by ID.
     *
     * @var array
     */
    protected $dictionary = [];

    /**
     * A buffer of dynamic calls to query macros.
     *
     * @var array
     */
    protected $macroBuffer = [];

    /**
     * Create a new morph to relationship instance.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $query
     * @param  \FluentGemini\Framework\Database\Orm\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $type
     * @param  string  $relation
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $otherKey, $type, $relation)
    {
        $this->morphType = $type;

        parent::__construct($query, $parent, $foreignKey, $otherKey, $relation);
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (! $this->otherKey) {
            return;
        }

        return $this->query->first();
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->buildDictionary($this->models = Collection::make($models));
    }

    /**
     * Build a dictionary with the models.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Collection  $models
     * @return void
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $this->dictionary[$model->{$this->morphType}][$model->{$this->foreignKey}][] = $model;
            }
        }
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
        return $models;
    }

    /**
     * Associate the model instance to the given parent.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Model  $model
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function associate($model)
    {
        $this->parent->setAttribute($this->foreignKey, $model->getKey());

        $this->parent->setAttribute($this->morphType, $model->getMorphClass());

        return $this->parent->setRelation($this->relation, $model);
    }

    /**
     * Dissociate previously associated model from the given parent.
     *
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);

        $this->parent->setAttribute($this->morphType, null);

        return $this->parent->setRelation($this->relation, null);
    }

    /**
     * Get the results of the relationship.
     *
     * Called via eager load method of Eloquent query builder.
     *
     * @return mixed
     */
    public function getEager()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }

        return $this->models;
    }

    /**
     * Match the results for a given type to their parents.
     *
     * @param  string  $type
     * @param  \FluentGemini\Framework\Database\Orm\Collection  $results
     * @return void
     */
    protected function matchToMorphParents($type, Collection $results)
    {
        foreach ($results as $result) {
            if (isset($this->dictionary[$type][$result->getKey()])) {
                foreach ($this->dictionary[$type][$result->getKey()] as $model) {
                    $model->setRelation($this->relation, $result);
                }
            }
        }
    }

    /**
     * Get all of the relation results for a type.
     *
     * @param  string  $type
     * @return \FluentGemini\Framework\Database\Orm\Collection
     */
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);

        $key = $instance->getTable().'.'.$instance->getKeyName();

        $query = $this->replayMacros($instance->newQuery())
            ->mergeModelDefinedRelationConstraints($this->getQuery())
            ->with($this->getQuery()->getEagerLoads());

        return $query->whereIn($key, $this->gatherKeysByType($type)->all())->get();
    }

    /**
     * Gather all of the foreign keys for a given type.
     *
     * @param  string  $type
     * @return array
     */
    protected function gatherKeysByType($type)
    {
        $foreign = $this->foreignKey;

        return collect($this->dictionary[$type])->map(function ($models) use ($foreign) {
            return head($models)->{$foreign};
        })->values()->unique();
    }

    /**
     * Create a new model instance by type.
     *
     * @param  string  $type
     * @return \FluentGemini\Framework\Database\Orm\Model
     */
    public function createModelByType($type)
    {
        $class = $this->parent->getActualClassNameForMorph($type);

        return new $class;
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
     * Get the dictionary used by the relationship.
     *
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * Replay stored macro calls on the actual related instance.
     *
     * @param \FluentGemini\Framework\Database\Orm\Builder $query
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    protected function replayMacros(Builder $query)
    {
        foreach ($this->macroBuffer as $macro) {
            call_user_func_array([$query, $macro['method']], $macro['parameters']);
        }

        return $query;
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        try {
            return parent::__call($method, $parameters);
        }

        // If we tried to call a method that does not exist on the parent Builder instance,
        // we'll assume that we want to call a query macro (e.g. withTrashed) that only
        // exists on related models. We will just store the call and replay it later.
        catch (BadMethodCallException $e) {
            $this->macroBuffer[] = compact('method', 'parameters');

            return $this;
        }
    }
}
