@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">文章管理</div>
                <div class="panel-body">
                    <a href="{{ url('admin/article/create') }}" class="btn btn-lg btn-primary">新增</a>

                    @foreach ($articles as $article)
                        <hr>
                        <div class="article">
                            <h4>{{ $article->title }}</h4>
                            <div class="content">
                                <p>
                                    {{ $article->body }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ url('admin/article/'.$article->id.'/edit') }}" class="btn btn-success">编辑</a>
                        <form action="{{ url('admin/article/'.$article->id) }}" method="POST" style="display: inline;">
                            <input name="_method" type="hidden" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection