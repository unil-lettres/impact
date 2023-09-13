<div
    class='finder'
    x-cloak
>
    <div wire:loading.delay.longest>
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
    <div class="d-flex row-height">
        <div class='column-large px-1'>{{ trans('courses.finder.name') }}</div>
        <div class='column-small px-1 d-none d-sm-block'>
            {{ trans('courses.finder.state') }}</div>
        <div class='column-small px-1 d-none d-xl-block'>
            {{ trans('courses.finder.created') }}</div>
        <div class='column-medium px-1 d-none d-lg-block'>
            {{ trans('courses.finder.editors') }}</div>
        <div class='column-medium px-1 d-none d-lg-block'>
            {{ trans('courses.finder.tags') }}</div>
    </div>
    <ul
        class="finder-selectable-list"
        x-data="{selectedItems: []}"
    >
        @foreach ($this->rows as $row)
            @if ($row->getType() === ('App\\Enums\\FinderRowType')::Folder)
                <x-finder.folder :folder="$row" />
            @else
                <x-finder.card :card="$row" />
            @endif
        @endforeach
    </ul>
    <div class="border-top"></div>
</div>
