@extends('Blessing\ImportV2Data::master')

@section('content')
<h1>欢迎</h1>

<p>欢迎使用 Blessing Skin Server 数据迁移工具，此工具用于迁移 v2 的数据至 v3。</p>
<p>目前支持导入 v2 的用户数据和皮肤至 v3 的皮肤库中。</p>

<hr />

<p class="step">
    <a href="{{ url('setup/migrations/import') }}" class="button button-large">下一步</a>
</p>
@endsection
