<?php

namespace App\Http\Requests;

use App\Enums\CardBox;
use App\Enums\ExportFormat;
use Illuminate\Validation\Rule;

class CreateCardExport extends AbstractRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'card' => 'required|integer|exists:cards,id',
            'box' => [
                'required',
                'string',
                Rule::in(
                    [CardBox::Box2]
                ),
            ],
            'format' => [
                'required',
                'string',
                Rule::in(
                    [ExportFormat::Docx]
                ),
            ],
        ];
    }
}
