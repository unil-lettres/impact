<?php

namespace App\Http\Requests;

use App\Enums\UsersFilter;
use Illuminate\Validation\Rule;

class ManageUsers extends AbstractRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filter' => [
                'string',
                Rule::in(
                    [UsersFilter::Expired, UsersFilter::Aai, UsersFilter::Local]
                ),
            ],
        ];
    }
}
