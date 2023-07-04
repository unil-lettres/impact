@props(['tags' => []])

<table class="table">
  <thead>
    <tr>
      <th scope="col">{{ trans('tags.name') }}</th>
      <th scope="col">{{ trans('tags.cards_count') }}</th>
    </tr>
  </thead>
  <tbody>
@forelse ($tags as $tag)
    <tr>
      <td>{{ $tag->name }}</td>
      <td>{{ $tag->cards->count() }}</td>
    </tr>
@empty
    <div>
        Aucun tag
    </div>
@endforelse
  </tbody>
</table>

