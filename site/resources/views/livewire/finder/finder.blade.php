<div class='finder' x-cloak>
    <div wire:loading>
        <div
            class='d-flex justify-content-center align-items-center modal-backdrop fade show'>
            <div>
                <div
                    class='spinner-grow text-niagara'
                    role='status'
                >
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex">
        <div class='column-large px-1'>{{trans('courses.finder.name')}}</div>
        <div class='column-medium px-1 d-none d-lg-block'>{{trans('courses.finder.editors')}}</div>
        <div class='column-small px-1 d-none d-sm-block'>{{trans('courses.finder.state')}}</div>
        <div class='column-small px-1 d-none d-lg-block'>{{trans('courses.finder.created')}}</div>
        <div class='column-medium px-1 d-none d-lg-block'>{{trans('courses.finder.tags')}}</div>
    </div>
    <ul class="finder-selectable-list">
        @foreach ($this->rows as $row)
            @if ($row->getType() === 'App\\Enums\\FinderRowType'::Folder)
                <livewire:finder.folder :folder="$row" />
            @else
                <livewire:finder.card :card="$row" />
            @endif
        @endforeach
    </ul>
</div>
