<option value="{{ \App\Enums\StatePermission::EditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::EditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission2') }}
</option>
<option value="{{ \App\Enums\StatePermission::ManagersAndEditorsCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::ManagersAndEditorsCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission3') }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowManagersAndEditorsCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowManagersAndEditorsCanEdit ? 'selected' : '' }}>
    {{ trans('states.permission4') }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowManagersCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowManagersCanEdit ? 'selected' : '' }}>
    {{ trans('states.permission5') }}
</option>
<option value="{{ \App\Enums\StatePermission::ManagersCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::ManagersCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission6') }}
</option>
