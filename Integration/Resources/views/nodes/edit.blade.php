@extends(config('app.theme').'.front.template')

@section('content')
    <div class="card-header">Редактировать узел</div>

    <div class="card-body">
        <form method="post" action="{{ route('integrations.nodes.update', [$integration->id, $node->id]) }}" >
            {{ csrf_field() }}
            {{ method_field('PUT') }}

            <div class="form-group">
                <label>@lang('integrations.node_name')</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $node->name or old('name') }}" required />
            </div>
            <input type="hidden" name="integration_id" value="{{ $integration->id }}" />
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="@lang('site.submit')" />
            </div>

        </form>
    </div>
@endsection
