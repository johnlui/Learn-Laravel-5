@extends('_layouts.default')

@section('content')
  <h4>
    <a href="/">⬅️返回首页</a>
  </h4>

  <h1 style="text-align: center; margin-top: 50px;">{{ $page->title }}</h1>
  <hr>
  <div id="date" style="text-align: right;">
    {{ $page->updated_at }}
  </div>
  <div id="content" style="padding: 50px;">
    <p>
      {{ $page->body }}
    </p>
  </div>
@endsection
