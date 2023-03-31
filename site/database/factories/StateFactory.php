<?php

namespace Database\Factories;

use App\Course;
use App\Enums\StatePermission;
use App\Enums\StateType;
use App\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = State::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = Carbon::now();

        return [
            'name' => fake()->word(),
            'position' => fake()->randomDigitNotNull(),
            'course_id' => Course::factory(),
            'permissions' => json_decode(State::PERMISSIONS),
            'actions' => json_decode(State::ACTIONS),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
    }

    /**
     * Indicate that the open state.
     *
     * @return Factory
     */
    public function open()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => trans('states.open'),
                'description' => trans('states.open_description'),
                'position' => 1,
                'permissions' => '{
                    "version": 1,
                    "box1": '.StatePermission::TeachersAndEditorsCanShowAndEdit.',
                    "box2": '.StatePermission::TeachersAndEditorsCanShowAndEdit.',
                    "box3": '.StatePermission::TeachersAndEditorsCanShowAndEdit.',
                    "box4": '.StatePermission::TeachersAndEditorsCanShowAndEdit.',
                    "box5": '.StatePermission::TeachersAndEditorsCanShowAndEdit.'
                }',
            ];
        });
    }

    /**
     * Indicate that the public state.
     *
     * @return Factory
     */
    public function public()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => trans('states.public'),
                'description' => trans('states.public_description'),
                'position' => 2,
                'permissions' => '{
                    "version": 1,
                    "box1": '.StatePermission::AllCanShowTeachersAndEditorsCanEdit.',
                    "box2": '.StatePermission::AllCanShowTeachersAndEditorsCanEdit.',
                    "box3": '.StatePermission::AllCanShowTeachersAndEditorsCanEdit.',
                    "box4": '.StatePermission::AllCanShowTeachersAndEditorsCanEdit.',
                    "box5": '.StatePermission::AllCanShowTeachersAndEditorsCanEdit.'
                }',
            ];
        });
    }

    /**
     * Indicate that the state has a private type.
     *
     * @return Factory
     */
    public function private()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => trans('states.private'),
                'description' => trans('states.private_description'),
                'position' => 0,
                'type' => StateType::Private,
            ];
        });
    }

    /**
     * Indicate that the state has an archived type.
     *
     * @return Factory
     */
    public function archived()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => trans('states.archived'),
                'description' => trans('states.archived_description'),
                'position' => 1000,
                'type' => StateType::Archived,
                'permissions' => '{
                    "version": 1,
                    "box1": '.StatePermission::TeachersCanShowAndEditEditorsCanShow.',
                    "box2": '.StatePermission::TeachersCanShowAndEditEditorsCanShow.',
                    "box3": '.StatePermission::TeachersCanShowAndEditEditorsCanShow.',
                    "box4": '.StatePermission::TeachersCanShowAndEditEditorsCanShow.',
                    "box5": '.StatePermission::TeachersCanShowAndEditEditorsCanShow.'
                }',
            ];
        });
    }
}
