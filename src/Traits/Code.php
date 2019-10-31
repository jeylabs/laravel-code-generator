<?php


namespace Jeylabs\CodeGenerator\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

/**
 * Trait Code
 * @package App\Traits\Eloquent
 */
trait Code
{
    /**
     * Trait boot method
     */
    public static function bootCode()
    {
        static::creating(function ($model) {
            $model->setAttribute(
                $model->getCodeColumn(),
                $model->generateCode()
            );
        });
    }

    /**
     * Generate code for model
     * @return string
     */
    private function generateCode()
    {
        $prefix = $this->getPrefix();
        $lastRecord = $this->getLastRecord($prefix);
        if (!$lastRecord) {
            $number = 0;
        } else {
            $number = preg_replace('/\D/', '', $lastRecord->getAttribute($this->getCodeColumn()));
        }
        return $prefix . sprintf('%07d', intval($number) + 1);
    }

    /**
     * get the code column
     * @return string
     */
    private function getCodeColumn()
    {
        return (isset(static::$codeColumn)) ? static::$codeColumn : "code";
    }

    /**
     * get last record of the model
     * @param $prefix
     * @return mixed
     */
    private function getLastRecord($prefix)
    {
        if (isset(static::$codePrefix) && is_array(static::$codePrefix)) {
            if (isset(static::$codePrefix[$prefix])) {
                $filter = static::$codePrefix[$prefix];
                $column = Arr::get($filter, 'column');
                $value = Arr::get($filter, 'value');
                if (collect(class_uses(__CLASS__))->contains(SoftDeletes::class)) {
                    return $this->newQuery()->where($column, $value)->withTrashed()->latest()->first();
                }
                return $this->newQuery()->where($column, $value)->latest()->first();
            }
        }
        if (collect(class_uses(__CLASS__))->contains(SoftDeletes::class)) {
            return $this->newQuery()->withTrashed()->latest()->first();
        }
        return $this->newQuery()->latest()->first();
    }

    /**
     * get the code prefix
     * @return int|string|null
     */
    private function getPrefix()
    {
        if (!isset(static::$codePrefix)) {
            return $this->getDefaultCode();
        }
        if (is_array(static::$codePrefix)) {
            $codePrefix = null;
            $availableColumns = $this->getAvailableColumns();
            foreach (static::$codePrefix as $prefix => $filter) {
                $column = Arr::get($filter, 'column');
                $value = Arr::get($filter, 'value');
                if (!in_array($column, $availableColumns) || !$value) continue;
                $modelValue = $this->getAttribute($column);
                if ($modelValue == $value) {
                    $codePrefix = $prefix;
                    break;
                }
            }
            return $codePrefix ? $codePrefix : $this->getDefaultPrefix();
        }
        return static::$codePrefix;
    }

    /**
     * get default prefix
     * @return string
     */
    private function getDefaultPrefix()
    {
        return strtoupper(
            substr(class_basename($this), 0, 3)
        );
    }

    /**
     * get all available columns
     * @return mixed
     */
    private function getAvailableColumns()
    {
        return Schema::getColumnListing($this->getTable());
    }
}
