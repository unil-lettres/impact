<?php

namespace App\Http\Requests;

use App\Enums\ActionType;
use App\Enums\StatePermission;
use Illuminate\Validation\Rule;

class UpdateState extends AbstractRequest
{
    const BOX_ALLOWED_PERMISSIONS = [
        StatePermission::EditorsCanShowAndEdit,
        StatePermission::TeachersAndEditorsCanShowAndEdit,
        StatePermission::AllCanShowTeachersAndEditorsCanEdit,
        StatePermission::AllCanShowTeachersCanEdit,
        StatePermission::TeachersCanShowAndEdit,
    ];

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
            'course' => 'required|integer|exists:courses,id',
            'state' => 'required|integer|exists:states,id',
            'name' => 'string|required|max:255',
            'description' => 'string|max:3000|nullable',
            'teachers_only' => [
                'sometimes',
                Rule::in(['on']),
            ],
            'box1' => [
                'required',
                'integer',
                Rule::in(self::BOX_ALLOWED_PERMISSIONS),
            ],
            'box2' => [
                'required',
                'integer',
                Rule::in(self::BOX_ALLOWED_PERMISSIONS),
            ],
            'box3' => [
                'required',
                'integer',
                Rule::in(self::BOX_ALLOWED_PERMISSIONS),
            ],
            'box4' => [
                'required',
                'integer',
                Rule::in(self::BOX_ALLOWED_PERMISSIONS),
            ],
            'box5' => [
                'required',
                'integer',
                Rule::in(self::BOX_ALLOWED_PERMISSIONS),
            ],
            'action-type' => [
                'required',
                'string',
                Rule::in(
                    [ActionType::None, ActionType::Email]
                ),
            ],
            'action-email-subject' => [
                'string',
                'max:255',
                Rule::requiredIf(
                    fn () => \request()->get('action-type') == ActionType::Email
                ),
            ],
            'action-email-message' => [
                'string',
                'max:3000',
                Rule::requiredIf(
                    fn () => \request()->get('action-type') == ActionType::Email
                ),
            ],
        ];
    }
}
