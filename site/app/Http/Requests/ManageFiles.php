<?php

namespace App\Http\Requests;

use App\Enums\FileStatus;
use Illuminate\Validation\Rule;

class ManageFiles extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'search' => ['string', 'max:255'],
            'filter' => [
                'string',
                Rule::in(
                    [FileStatus::Ready, FileStatus::Processing, FileStatus::Transcoding, FileStatus::Failed]
                ),
            ],
        ];
    }
}
