<option value="{{ \App\Enums\StatePermission::HoldersCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::HoldersCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission2') }}
</option>
<option value="{{ \App\Enums\StatePermission::ManagersAndHoldersCanShowAndEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::ManagersAndHoldersCanShowAndEdit ? 'selected' : '' }}>
    {{ trans('states.permission3') }}
</option>
<option value="{{ \App\Enums\StatePermission::AllCanShowManagersAndHoldersCanEdit }}"
    {{ $activeState->permissions[$box] == \App\Enums\StatePermission::AllCanShowManagersAndHoldersCanEdit ? 'selected' : '' }}>
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
