<?php

namespace FluentGemini\Framework\Database\Orm\Relations;

use FluentGemini\Framework\Database\Orm\Model;
use FluentGemini\Framework\Database\Orm\Builder;
use FluentGemini\Framework\Database\Orm\Collection;
use FluentGemini\Framework\Database\Query\Expression;
use FluentGemini\Framework\Database\Orm\SoftDeletes;
use FluentGemini\Framework\Database\Orm\ModelNotFoundException;

class HasManyThrough extends Relation
{
    /**
     * The distance parent model instance.
     *
     * @var \FluentGemini\Framework\Database\Orm\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    protected $parentRelationKey;

    /**
     * Create a new has many through relationship instance.
     *
     * @param \FluentGemini\Framework\Database\Orm\Builder $query
     * @param \FluentGemini\Framework\Database\Orm\Model $farParent
     * @param \FluentGemini\Framework\Database\Orm\Model $parent
     * @param string $firstKey
     * @param string $secondKey
     * @param string $localKey
     * @return void
     */
    public function __construct(Builder $query, Model $farParent, Model $parent, $firstKey, $secondKey, $localKey, $parentRelationKey = 'id')
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->parentRelationKey = $parentRelationKey;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $parentTable = $this->parent->getTable();

        $localValue = $this->farParent[$this->localKey];

        $this->setJoin();

        if (static::$constraints) {
            $this->query->where($parentTable . '.' . $this->firstKey, '=', $localValue);
        }
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param \FluentGemini\Framework\Database\Orm\Builder $query
     * @param \FluentGemini\Framework\Database\Orm\Builder $parent
     * @param array|mixed $columns
     * @return \FluentGemini\Framework\Database\Orm\Builder
     */
    public function getRelationQuery(Builder $query, Builder $parent, $columns = ['*'])
    {
        $parentTable = $this->parent->getTable();

        $this->setJoin($query);

        $query->select($columns);

        $key = $this->wrap($parentTable . '.' . $this->firstKey);

        return $query->where($this->getHasCompareKey(), '=', new Expression($key));
    }

    /**
     * Set the join clause on the query.
     *
     * @param \FluentGemini\Framework\Database\Orm\Builder|null $query
     * @return void
     */
    protected function setJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $foreignKey = $this->related->getTable() . '.' . $this->secondKey;

        $query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);

        if ($this->parentSoftDeletes()) {
            $query->whereNull($this->parent->getQualifiedDeletedAtColumn());
        }
    }

    public function getQualifiedParentKeyName()
    {
        return $this->parent->getTable().'.'.$this->parentRelationKey;
    }

    /**
     * Determine whether close parent of the relation uses Soft Deletes.
     *
     * @return bool
     */
    public function parentSoftDeletes()
    {
        return in_array(SoftDeletes::class, $this->class_uses_recursive(get_class($this->parent)));
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $table = $this->parent->getTable();

        $this->query->whereIn($table . '.' . $this->firstKey, $this->getKeys($models));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array $models
     * @param string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array $models
     * @param \FluentGemini\Framework\Database\Orm\Collection $results
     * @param string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getKey();

            if (isset($dictionary[$key])) {
                $value = $this->related->newCollection($dictionary[$key]);

                $model->setRelation($relation, $value);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param \FluentGemini\Framework\Database\Orm\Collection $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        $foreign = $this->firstKey;

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $dictionary[$result->{$foreign}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    /**
     * Execute the query and get the first related model.
     *
     * @param array $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param array $columns
     * @return \FluentGemini\Framework\Database\Orm\Model|static
     *
     * @throws \FluentGemini\Framework\Database\Orm\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if (!is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->parent));
    }

    /**
     * Find a related model by its primary key.
     *
     * @param mixed $id
     * @param array $columns
     * @return \FluentGemini\Framework\Database\Orm\Model|\FluentGemini\Framework\Database\Orm\Collection|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }

        $this->where($this->getRelated()->getQualifiedKeyName(), '=', $id);

        return $this->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param mixed $ids
     * @param array $columns
     * @return \FluentGemini\Framework\Database\Orm\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        $this->whereIn($this->getRelated()->getQualifiedKeyName(), $ids);

        return $this->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
     *
     * @param mixed $id
     * @param array $columns
     * @return \FluentGemini\Framework\Database\Orm\Model|\FluentGemini\Framework\Database\Orm\Collection
     *
     * @throws \FluentGemini\Framework\Database\Orm\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (is_array($id)) {
            if (count($result) == count(array_unique($id))) {
                return $result;
            }
        } elseif (!is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->parent));
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     * @return \FluentGemini\Framework\Database\Orm\Collection
     */
    public function get($columns = ['*'])
    {
        // First we'll add the proper select columns onto the query so it is run with
        // the proper columns. Then, we will get the results and hydrate out pivot
        // models with the result of those columns as a separate model relation.
        $columns = $this->query->getQuery()->columns ? [] : $columns;

        $select = $this->getSelectColumns($columns);

        $builder = $this->query->applyScopes();

        $models = $builder->addSelect($select)->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param array $columns
     * @return array
     */
    protected function getSelectColumns(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge($columns, [$this->parent->getTable() . '.' . $this->firstKey]);
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int $page
     * @return \FluentGemini\Framework\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @return \FluentGemini\Framework\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page')
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        return $this->query->simplePaginate($perPage, $columns, $pageName);
    }

    /**
     * Get the key for comparing against the parent key in "has" query.
     *
     * @return string
     */
    public function getHasCompareKey()
    {
        return $this->farParent->getQualifiedKeyName();
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->related->getTable() . '.' . $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
     *
     * @return string
     */
    public function getThroughKey()
    {
        return $this->parent->getTable() . '.' . $this->firstKey;
    }

    public function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += $this->trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    public function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += $this->trait_uses_recursive($trait);
        }

        return $traits;
    }
}
