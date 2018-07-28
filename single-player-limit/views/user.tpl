@extends('user.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
  <h1>
    {{ trans('general.dashboard') }}
    <small>Overview</small>
  </h1>
  </section>

  <!-- Main content -->
  <section class="content">
  @if (option('require_verification') && Schema::hasColumn('users', 'verified'))
    @if (! $user->verified)
      @include('common.email-verification')
    @endif
  @endif
  <div class="row">
    <div class="col-md-8">
    <div class="box box-primary">
      <div class="box-header with-border">
      <h3 class="box-title">{{ trans('user.used.title') }}</h3>
      </div><!-- /.box-header -->
      <div class="box-body">
      <div class="row">
        <div class="col-md-8">
        <table class="table">
          <tbody>
          <tr>
            <td class="key">{{ trans('user.player.player-name') }}</td>
            <td class="value">
            <div class="input-group">
              <input type="text" {{ option('allow_change_player_name') ? '' : 'disabled="disabled"' }} class="form-control" id="player-name" value="{{ $player['player_name'] }}">
              <span class="input-group-btn">
              <button id="change-player-name" class="btn btn-default form-control" type="button">更改角色名</button>
              </span>
            </div>
            </td>
          </tr>

          <tr>
            <td class="key">
            {{ trans('user.player.preference.title') }}
            </td>
            <td class="value">
             <select class="form-control" id="preference" pid="{{ $player['pid'] }}">
               <option {{ ($player['preference'] == "default") ? 'selected="selected"' : '' }} value="default">Default</option>
               <option {{ ($player['preference'] == "slim") ? 'selected="selected"' : '' }} value="slim">Slim</option>
            </select>
            </td>
          </tr>

          <tr>
            <td class="key">
            {{ trans('user.used.storage') }}
            </td>
            <td class="value">
            <div class="progress" data-toggle="tooltip" data-placement="top" title="{{ $storage['used'] }}/{{ $storage['total'] }} KB">
              <div class="progress-bar progress-bar-warning progress-bar-striped" id="user-storage-bar" role="progressbar" style="width: {{ $storage['percentage'] }}%;min-width: 3em;">
              {{ round($storage['percentage'], 1) }}%
              </div>
            </div>
            </td>
          </tr>
          </tbody>
        </table>
        </div><!-- /.col -->
        <div class="col-md-4">
        <p class="text-center">
          <strong>{{ trans('user.cur-score') }}</strong>
        </p>
        <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
          {{ $user->getScore() }}
        </p>
        <p class="text-center" style="font-size: smaller; margin-top: 20px;">{{ trans('user.score-notice') }}</p>
        </div><!-- /.col -->
      </div><!-- /.row -->
      </div><!-- ./box-body -->
      <div class="box-footer">
      @if ($user->canSign())
      <button id="sign-button" class="btn btn-primary pull-left" onclick="sign()">
        <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;{{ trans('user.sign') }}
      </button>
      @else
      <button class="btn btn-primary pull-left" title="{{ trans('user.last-sign', ['time' => $user->getLastSignTime()]) }}" disabled="disabled">
        <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;
        <?php $hours = $user->getSignRemainingTime() / 3600; ?>
        @if ($hours >= 1)
        {{ trans('user.sign-remain-time', ['time' => round($hours), 'unit' => trans('user.time-unit-hour')]) }}
        @else
        {{ trans('user.sign-remain-time', ['time' => round($hours * 60), 'unit' => trans('user.time-unit-min')]) }}
        @endif
      </button>
      @endif

      <a class="btn btn-warning pull-right" href="javascript:clearTexture('{{ $player['pid'] }}');">{{ trans('user.player.delete-texture') }}</a>
      </div><!-- /.box-footer -->
    </div><!-- /.box -->

    <div class="box box-default">
      <div class="box-header with-border">
      <h3 class="box-title">{{ trans('user.announcement') }}</h3>
      </div><!-- /.box-header -->
      <div class="box-body">
      {!! bs_announcement() !!}
      </div><!-- /.box-body -->
    </div><!-- /.box -->
    </div>
    <div class="col-md-4">
    <div class="box">
      <!-- 3D skin preview -->
      @include('common.texture-preview', ['title' => trans('user.player.player-info') ])
      <!-- 2D skin preview -->
      <div class="box-body">
      <div id="preview-2d-container" style="display: none;">
      <p>{{ trans('user.player.textures.steve') }}<a href=""><img id="steve" class="skin2d" /></a>
        <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
      </p>

      <p>{{ trans('user.player.textures.alex') }}<a href=""><img id="alex" class="skin2d" /></a>
        <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
      </p>

      <p>{{ trans('user.player.textures.cape') }}<a href=""><img id="cape" class="skin2d" /></a>
        <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
      </p>
      </div>
      </div><!-- /.box-body -->
      <div class="box-footer">
      <button id="preview-switch" class="btn btn-default">{{ trans('general.switch-2d-preview') }}</button>
      </div>
    </div><!-- /.box -->
    </div>
  </div>

  </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-score-instruction" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">{{ trans('user.score-intro.title') }}</h4>
    </div>
    <div class="modal-body">
    <?php list($from, $to) = explode(',', Option::get('sign_score')); ?>
    {!! nl2br(trans('user.score-intro.introduction', [
      'initial_score' => option('user_initial_score'),
      'score-from'  => $from,
      'score-to'  => $to,
      'return-score'  => option('return_score') ? trans('user.score-intro.will-return-score') : trans('user.score-intro.no-return-score')
    ])) !!}

    <hr />

    <div class="row">
      <div class="col-md-6">
      <p class="text-center">{{ trans('user.score-intro.rates.storage', ['score' => option('score_per_storage')]) }}</p>
      </div>
      <div class="col-md-6">
      <p class="text-center">{{ trans('user.score-intro.rates.player', ['score' => option('score_per_player')]) }}</p>
      </div>
    </div>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
    </div>
  </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('script')
<script>
  $(document).ready(function () {
    $.msp.config.skinUrl = defaultSteveSkin;
    initSkinViewer();
    registerAnimationController();
    registerWindowResizeHandler();
    showPlayerTexturePreview({{ $player->pid }});
  });

  blessing.currentPlayerName = '{{ $player['player_name'] }}';
  blessing.allowChangePlayerName = {!! json_encode((bool) option('allow_change_player_name')) !!};
</script>
<script src="{{ plugin_assets('single-player-limit', 'assets/dist/user.js') }}"></script>
@endsection
