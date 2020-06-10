<div id="files">
    <div class="card">
        <div class="card-header">
            <span class="title">{{ trans('files.files') }} <span class="badge badge-secondary">{{ $files->total() }}</span></span>
            <a href="{{ route('admin.files.create') }}"
               class="btn btn-primary float-right">
                {{ trans('files.create') }}
            </a>
        </div>
        <div class="card-body">
            @if ($files->items())
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ trans('files.name') }}</th>
                        <th>{{ trans('files.type') }}</th>
                        <th>{{ trans('files.status') }}</th>
                        <th>{{ trans('files.created_at') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($files->items() as $file)
                        <tr>
                            <td>
                                <a href="{{ Helpers::fileUrl($file->filename) }}" target="_blank">
                                    {{ Helpers::truncate($file->name) }}
                                </a>
                            </td>
                            <td>{{ Helpers::fileType($file->type) }}</td>
                            <td>{!! Helpers::fileStatusBadge($file->status) !!}</td>
                            <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="actions">
                                <span>
                                    <a href="{{ route('admin.files.edit', $file->id) }}"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       class="btn btn-primary"
                                       title="{{ trans('files.edit') }}">
                                        <i class="far fa-edit"></i>
                                    </a>
                                </span>
                                <span>
                                    <form class="with-delete-confirm" method="post"
                                          action="{{ route('admin.files.destroy', $file->id) }}">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-danger"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                title="{{ trans('files.delete') }}">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $files->onEachSide(1)->links() }}
            @else
                <p class="text-secondary">
                    {{ trans('files.not_found') }}
                </p>
            @endif
        </div>
    </div>
</div>
