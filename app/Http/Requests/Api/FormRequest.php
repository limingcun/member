<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest as DingoFormRequest;

class FormRequest extends DingoFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function filterBoolean($columns)
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            if (\Input::get($column)) {
                \Input::merge([$column => filter_var(\Input::get($column), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }

    protected function filterJson($columns)
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            if (\Input::get($column) && is_string(\Input::get($column))) {
                if ($json = json_decode(\Input::get($column), true)) {
                    \Input::merge([$column => $json]);
                }
            }
        }
    }
}
