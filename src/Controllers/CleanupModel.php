<?php

namespace Engelsystem\Controllers;

use ArrayAccess;
use Illuminate\Database\Eloquent\Model;

trait CleanupModel
{
    /**
     * Used to replace null values with en empty string
     *
     * Required because isset on a null value returns false which gets interpreted as missing properties by Twig
     *
     * @param Model[]|ArrayAccess|Model $models
     * @param string[]                  $attributes
     */
    protected function cleanupModelNullValues($models, array $attributes = [])
    {
        if (!$models) {
            return;
        }

        $models = $models instanceof Model ? [$models] : $models;
        foreach ($models as $model) {
            /** @var Model $model */
            $attributes = $attributes ?: array_merge(
                array_keys($model->getAttributes()),
                array_keys($model->getCasts()),
                $model->getFillable(),
                $model->getDates()
            );
            foreach ($attributes as $attribute) {
                $model->$attribute = is_null($model->$attribute) ? '' : $model->$attribute;
            }
        }
    }
}
