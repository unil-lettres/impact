<div id="files">
    <div class="card">
        <div class="card-header">
            <!-- TODO: add translation -->
            <span class="title">Fichiers <span class="badge badge-secondary">{{ $files->total() }}</span></span>
        </div>
        <div class="card-body">
            @if ($files->items())
                <table class="table">
                    <thead>
                    <tr>
                        <!-- TODO: add translations -->
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Date de création</th>
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
                            <td>{{ $file->type }}</td>
                            <td>{{ $file->status }}</td>
                            <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="actions">
                                <span>
                                    <!-- TODO: add translation -->
                                    <a href="{{ route('admin.files.edit', $file->id) }}"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       class="btn btn-primary"
                                       title="Editer">
                                        <i class="far fa-edit"></i>
                                    </a>
                                </span>
                                <span>
                                    <form class="with-delete-confirm" method="post"
                                          action="{{ route('admin.files.destroy', $file->id) }}">
                                        @method('DELETE')
                                        @csrf
                                        <!-- TODO: add translation -->
                                        <button type="submit"
                                                class="btn btn-danger"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                title="Supprimer">
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
                    <!-- TODO: add translation -->
                    Aucun fichier trouvé
                </p>
            @endif
        </div>
    </div>
</div>
