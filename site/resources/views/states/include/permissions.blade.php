<option value="{{ \App\Enums\StatePermission::EditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::EditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ Helpers::permissionLabel(\App\Enums\StatePermission::EditorsCanShowAndEdit) }}
</option>
<option value="{{ \App\Enums\StatePermission::TeachersAndEditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::TeachersAndEditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ Helpers::permissionLabel(\App\Enums\StatePermission::TeachersAndEditorsCanShowAndEdit) }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowTeachersAndEditorsCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowTeachersAndEditorsCanEdit ? 'selected' : '' }}>
    {{ Helpers::permissionLabel(\App\Enums\StatePermission::AllCanShowTeachersAndEditorsCanEdit) }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowTeachersCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowTeachersCanEdit ? 'selected' : '' }}>
    {{ Helpers::permissionLabel(\App\Enums\StatePermission::AllCanShowTeachersCanEdit) }}
</option>
<option value="{{ \App\Enums\StatePermission::TeachersCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::TeachersCanShowAndEdit ? 'selected' : '' }}>
    {{ Helpers::permissionLabel(\App\Enums\StatePermission::TeachersCanShowAndEdit) }}
</option>
