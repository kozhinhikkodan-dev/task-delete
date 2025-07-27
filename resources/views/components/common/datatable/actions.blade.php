<div class="d-flex gap-2">
    @foreach ($actions as $action)
        @if ($action['label'] === 'view')
            @can('view', $model)
                <a href="{{ route($action['route'], $model->id ?? 1) }}" class="btn btn-light btn-sm">
                    <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                </a>
            @endcan
        @elseif ($action['label'] === 'edit')
            @can('update', $model)
                <a href="{{ route($action['route'], $model->id ?? 1) }}" class="btn btn-soft-primary btn-sm">
                    <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                </a>
            @endcan
        @elseif ($action['label'] === 'delete')
            @can('delete', $model)
                <a href="{{ route($action['route'], $model->id ?? 1) }}" class="btn btn-soft-danger btn-sm"
                   onclick="event.preventDefault(); document.getElementById('delete-form-{{ $model->id }}').submit();">
                    <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                </a>

                <form id="delete-form-{{ $model->id }}" action="{{ route($action['route'], $model->id ?? 1) }}"
                      method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endcan
        @endif
    @endforeach
</div>
