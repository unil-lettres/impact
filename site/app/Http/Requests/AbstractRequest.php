<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractRequest extends FormRequest
{
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        return array_replace_recursive(
            parent::all(),
            $this->route()->parameters()
        );
    }
}
