<?php

namespace FluentGemini\Framework\Database\Orm;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \FluentGemini\Framework\Database\Orm\Builder  $builder
     * @param  \FluentGemini\Framework\Database\Orm\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
