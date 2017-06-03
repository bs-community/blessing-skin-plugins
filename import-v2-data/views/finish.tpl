@extends('Blessing\ImportV2Data::master')

@section('content')

<h1>导入成功</h1>

<p>已导入 {{ $result['user']['imported'] }} 个用户，{{ $result['user']['duplicated'] }} 个用户因重复而未导入。</p>
<p>已导入 {{ $result['texture']['imported'] }} 个材质到皮肤库，{{ $result['texture']['duplicated'] }} 个材质因重复而未导入。</p>

<p class="step">
<a href="../../" class="button button-large">导入完成</a>
</p>

@endsection
