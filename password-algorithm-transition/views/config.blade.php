@extends('admin.master')

@section('title', '密码算法转换')

@section('content')

@php
$users = App\Models\User::select(['password'])->get();
$count = User::count();
$info = User::groupBy(function ($user) {
    $password = $user->password;
    if (Illuminate\Support\Str::startsWith($password, '$2y')) {
        return 'Bcrypt';
    } else {
        $length = strlen($password);
        if ($length === 128) {
            return 'SHA512';
        } elseif ($length === 64) {
            return 'SHA256';
        } elseif ($length === 32) {
            return 'MD5';
        }
    }
})->map(function ($users, $algName) use ($count) {
    $total = count($users);
    $percentage = $total / $count * 100;
    return $algName.': '.$total.' 位用户 ('.$percentage.'%)';
});
@endphp

<div class="content-wrapper">
  <section class="content-header">
    <h1>密码算法转换</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box box-default">
          <div class="box-header">全站转换进度</div>
          <div class="box-body">
            <ul>
              @foreach($info as $item)
              <li>{{ $item }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection
