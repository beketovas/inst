@extends(config('app.theme').'.front.template')

@section('content')
    <div class="card-header">Добавим узел</div>

    <div class="card-body">
        <form method="post" action="{{ route('integrations.nodes.store', [$integration->id]) }}" >
            {{ csrf_field() }}

            <div class="form-group">
                <label>@lang('integrations.node_name')</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required />
            </div>
            <input type="hidden" name="integration_id" value="{{ $integration->id }}" />
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="@lang('site.submit')" />
            </div>

        </form>
    </div>
@endsection
