@extends('Blessing\ImportV2Data::master')

@section('content')

<h1>同时导入用户数据以及用户材质</h1>

<p>导入后材质的上传者将被设置为 v2 的原用户，上传时间将被设置为 v2 用户的最后修改时间。导入后的材质会被自动添加至原上传者的衣柜中，并应用至其所属角色。</p>
<p><b>注意：</b> 请先将 v2 的 users 表改名（不然会重名冲突）并导入到当前 v3 的同一数据库中（因为我没法访问除了皮肤站数据库以外的其他数据库啊，你又没给我账号密码）</p>

<hr />

<form id="setup" method="post" action="{{ url('setup/migrations/import') }}" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="v2_table_name">v2 的用户表名</label></th>
            <td>
                <input name="v2_table_name" type="v2_table_name" id="v2_table_name" size="25" value="" />
                <p>就是你改名过的 v2 的 users 表现在的名字</p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="texture_name_pattern">导入后的材质名称</label></th>
            <td>
                <input name="texture_name_pattern" type="text" id="texture_name_pattern" size="25" value="{username} - {model}" />
                <p>
                    <span class="description important">
                        {username} 表示材质原本的上传者用户名，{model} 表示原来材质的模型
                    </span>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">私密材质</th>
            <td>
                <label for="import_as_private">
                    <input name="import_as_private" type="checkbox" id="import_as_private" size="25" /> 导入为私密材质
                </label>
            </td>
        </tr>
    </table>

    @if (count($errors) > 0)
        <div class="alert alert-warning" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="step">
        <input type="submit" name="submit" id="submit" class="button button-large" value="开始迁移"  />
    </p>
</form>

@endsection
