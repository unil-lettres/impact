<div class="mb-3">
    <label for="{{$id}}-name" class="form-label">
        {{ trans('courses.name') }}
    </label>
    <input
        id="{{$id}}-name"
        type="text"
        class="form-control"
        wire:model="name"
        autocomplete="off"
    />
</div>
<div class="mb-3">
    <label for="{{$id}}-folder-id" class="control-label form-label">
        {{ trans('folders.location') }}
    </label>
    <select
        id="{{$id}}-folder-id"
        class="form-select"
        wire:model="destination"
    >
        <option value="{{$folder?->id}}">
            @if ($folder)
                {{ $folder->title.' '.trans('folders.location.current') }}
            @else
                {{ trans('courses.finder.dialog.rootFolder') }}
            @endif
        </option>
        @foreach($this->foldersDestination as $_folder)
            <option value="{{$_folder->id}}">
                {{ $_folder->titleFullPath }}
            </option>
        @endforeach
    </select>
</div>
