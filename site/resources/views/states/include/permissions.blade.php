<option value="{{ \App\Enums\StatePermission::EditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::EditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission2') }}
</option>
<option value="{{ \App\Enums\StatePermission::TeachersAndEditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::TeachersAndEditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission3') }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowTeachersAndEditorsCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowTeachersAndEditorsCanEdit ? 'selected' : '' }}>
    {{ trans('states.permission4') }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowTeachersCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowTeachersCanEdit ? 'selected' : '' }}>
    {{ trans('states.permission5') }}
</option>
<option value="{{ \App\Enums\StatePermission::TeachersCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::TeachersCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission6') }}
</option>
