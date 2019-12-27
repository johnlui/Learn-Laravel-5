在本篇文章中，我们将尝试构建一个带后台的简单博客系统。我们将会使用到 路由、MVC、Eloquent ORM 和 blade 视图系统。

## 简单博客系统规划
我们在教程一中已经新建了一个继承自 Eloquent Model 类的 Article 类，使用 migration 建立了数据表并使用 seeder 填入了测试数据。我们的博客系统暂时将只管理这一种资源：后台需要使用账号密码登录，进入后台之后，可以新增、修改、删除文章；前台显示文章列表，并在点击标题之后显示出文章全文。

下面我们正式开始。

## 搭建前台
前台的搭建是最简单的，我先带大家找找感觉。

### 修改路由
删掉

```
Route::get('/', function () {
    return date("Y-m-d H:i:s");
});
```

将`Route::get('/home', 'HomeController@index')->name('home');` 改为 `Route::get('/', 'HomeController@index')->name('home');`，现在我们系统的首页就落到了 `App\Http\Controllers\HomeController` 类的 `index` 方法上了。

### 查看 HomeController 的 index 函数
将 `learnlaravel5/app/Http/Controllers/HomeController.php` 的 index 函数我们之前加的 Exception 那行代码删除，就只剩一行代码了：`return view('home');`，这个很好理解，返回名叫 home 的视图给用户。这个视图文件在哪里呢？在 `learnlaravel5/resources/views/home.blade.php`，blade 是 Laravel 视图引擎的名字，会对视图文件进行加工。

### blade 浅析
blade 引擎会对视图文件进行预处理，帮我们简化一些重复性很高的 echo、foreach 等 PHP 代码。blade 还提供了一个灵活强大的视图组织系统。打开 `home.blade.php`：

```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### @extends('layouts.app')
这表示此视图的基视图是 `learnlaravel5/resources/views/layouts/app.blade.php` 。这个函数还隐含了一个小知识：在使用名称查找视图的时候，可以使用 . 来代替 / 或 \。

#### @section('content') ... @endsection
这两个标识符之间的代码，会被放到基视图的 `@yield('content')` 中进行输出。

### 访问首页
首先删除 `learnlaravel5/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php` 中 `dispatch` 函数里我们加的的 var_dump 代码，否则会出现奇怪的页面。
访问 http://fuck.io:1024 ，不出意外的话，你会看到这个页面：

![](https://camo.githubusercontent.com/be32acb9dae82b79ab062b51f210ade43d5409d8/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130323334383632313836332e6a7067)

为什么需要登录呢？怎么去掉这个强制登录呢？删掉 HomeController 中的构造函数即可：

```
public function __construct()
{
    $this->middleware('auth');
}
```

这个函数会在控制器类生成对象后第一时间自动载入一个名为 auth 的中间件，正是这一步导致了首页需要登录。删除构造函数之后，重新访问 http://fuck.io:1024 ，页面应该就会直接出来了。这里要注意两点：① 一定要重新访问，不要刷新，因为此时页面的 url 其实是 http://fuck.io:1024/login ② 这个页面跟之前的欢迎页虽然看起来一毛一样，但其实文字是不同的，注意仔细观察哦。

### 向视图文件输出数据
既然 Controller - View 的架构已经运行，下一步就是引入 Model 了。Laravel 中向视图传数据非常简单：

```
public function index()
{
    return view('home')->withArticles(\App\Article::all());
}
```

2016 版教程里很多人看到这段代码都十分不解，这里解释一下：

1. \App\Article::all() 是采用`绝对命名空间`方式对 Article 类的调用。
2. withArticles 是我定义的方法，Laravel 并不提供，这也是 Laravel 优雅的一个表现：Laravel View 采用 __call 来 handle 对未定义 function 的调用，其作用很简单：给视图系统注入一个名为 `$articles` 的变量，这段代码等价于 `->with('articles', \App\Article::all())`。
3. 展开讲一下，`->withFooBar(100)` 等价于 `->with('foo_bar', 100)`，即驼峰变量会被完全转换为蛇形变量。

### 修改视图文件
修改视图文件 `learnlaravel5/resources/views/home.blade.php` 的代码为：

```
@extends('layouts.app')

@section('content')
    <div id="title" style="text-align: center;">
        <h1>Learn Laravel 5</h1>
        <div style="padding: 5px; font-size: 16px;">Learn Laravel 5</div>
    </div>
    <hr>
    <div id="content">
        <ul>
            @foreach ($articles as $article)
            <li style="margin: 50px 0;">
                <div class="title">
                    <a href="{{ url('article/'.$article->id) }}">
                        <h4>{{ $article->title }}</h4>
                    </a>
                </div>
                <div class="body">
                    <p>{{ $article->body }}</p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
@endsection
```

刷新，得到：

![](https://camo.githubusercontent.com/039277caa2cdae51920d400d9deb1c31ba43b37e/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333036303335383031362e6a7067)

如果看到以上页面，恭喜你，Laravel 初体验成功！

### 调整视图
前台页面是不应该有顶部的菜单栏的，特别是还有注册、登录之类的按钮。接下来我们修改视图内容为：

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Learn Laravel 5</title>

    <link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

    <div id="title" style="text-align: center;">
        <h1>Learn Laravel 5</h1>
        <div style="padding: 5px; font-size: 16px;">Learn Laravel 5</div>
    </div>
    <hr>
    <div id="content">
        <ul>
            @foreach ($articles as $article)
            <li style="margin: 50px 0;">
                <div class="title">
                    <a href="{{ url('article/'.$article->id) }}">
                        <h4>{{ $article->title }}</h4>
                    </a>
                </div>
                <div class="body">
                    <p>{{ $article->body }}</p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

</body>
</html>
```

此视图文件变成了一个独立视图，不再有基视图，并且将 jQuery 和 BootStrap 替换为了国内的 CDN，更快更稳定了。

同理我们修改 `learnlaravel5/resources/views/layouts/app.blade.php`：

① 删除 `<script src="{{ asset('js/app.js') }}"></script>`
② 替换 `<link href="{{ asset('css/app.css') }}" rel="stylesheet">` 为

```
<link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
```

接下来我们来着手搭建后台。

## 搭建后台
### 生成控制器
我们使用 Artisan 工具来生成控制器文件及代码：

```shell
php artisan make:controller Admin/HomeController
```

成功之后，我们就可以看到 artisan 帮我们建立的文件夹及控制器文件了：

![](https://camo.githubusercontent.com/7810111fd8becaa97ace0c5cfc9bd8fa2bf3e5f6/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031362d30362d30332d31343633373636303031343230332e6a7067)

### 增加路由
我们要使用路由组来将后台页面置于“需要登录才能访问”的中间件下，以保证安全。在 web.php 里增加下面三行：

```
Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function() {
    Route::get('/', 'HomeController@index');
});
```

上一篇文章中我们已经接触到了路由组，这是 Laravel 的另一个伟大创造。路由组可以给组内路由一次性增加 命名空间、uri 前缀、域名限定、中间件 等属性，并且可以多级嵌套，异常强大。路由组中文文档在此：https://d.laravel-china.org/docs/5.5/routing#route-groups

上面的三行代码的功能简单概括就是：访问这个页面必须先登录，若已经登录，则将 `http://fuck.io:1024/admin` 指向 `App\Http\Controllers\Admin\HomeController` 的 index 方法。其中需要登录由 `middleware` 定义，`/admin` 由 `prefix` 定义，`Admin` 由 `namespace` 定义，`HomeController` 是实际的类名。

### 构建后台首页
#### 新建 index 方法
在新生成的 `learnlaravel5/app/Http/Controllers/Admin/HomeController.php` 文件中增加一个 function：

```
public function index()
{
    return view('admin/home');
}
```

#### 新建后台首页视图文件
在 `learnlaravel5/resources/views/` 目录下新建一个名为 admin 的文件夹，在 admin 内新建一个名为 `home.blade.php` 的文件，填入代码：

```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Learn Laravel 5 后台</div>

                <div class="panel-body">

                    <a href="{{ url('admin/article') }}" class="btn btn-lg btn-success col-xs-12">管理文章</a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### 修改 Auth 系统登陆成功之后的跳转路径
修改 `learnlaravel5/app/Http/Controllers/Auth/LoginController.php` 中相应的代码为：

```
protected $redirectTo = '/admin';
```

#### 尝试登录
访问 http://fuck.io:1024/admin ，它会跳转到登陆界面，输入邮箱和密码之后，你应该会看到如下页面：

![](https://camo.githubusercontent.com/f56c11b3924ac8e0bbf2c4d3aefa20376821bfce/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333039303236373233352e6a70673f743d33)

恭喜你，后台首页搭建完成！下面我们开始构建 Article 的后台管理功能。

### 构建 Article 后台管理功能
让我们先尝试点一下 “管理文章”按钮，不出意外你将得到一个报错：

![](https://camo.githubusercontent.com/1aa9f0b2d15610fdff06a5e73c902a53a980691d/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333039313137323634352e6a7067)

这是 Laravel 5.5 刚刚引入的新策略：404 错误不再报告详细信息，而是展示一个友好的 404 页面。

#### 添加路由
404 错误是访问了系统没有监听的路由导致的。下面我们要添加针对 `http://fuck.io:1024/admin/article` 的路由：

```
Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function() {
    Route::get('/', 'HomeController@index');
    Route::get('article', 'ArticleController@index');
});
```

刷新，出现详细报错信息了：

![](https://camo.githubusercontent.com/35814a132cd945a044f10f2bc73f35a11872da13/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333135333335353032312e6a7067)

#### 进步之道
很多新手看到这个报错直接就慌了：什么鬼？全是英文看不懂呀。然后在文章下面把完整的错误栈全部粘贴出来。老实说我第一次见到 Laravel 报这个错也是完全没耐心去读，不过我还是复制了最明显的那句话“Class App\Http\Controllers\Admin\ArticleController does not exist”去 Google 了一下，从此我就再也没搜索过它了。

如果你遇到了奇怪的报错，不要慌，稳住，Google 一下，我们能赢。

#### 新建控制器
上图中的报错是控制器不存在。我们使用 Artisan 来新建控制器：

```shell
php artisan make:controller Admin/ArticleController
```

刷新，错误又变了：

![](https://camo.githubusercontent.com/eef2a12383699c72b7923af6214fa7b1a8567032/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333135343939313933332e6a70673f743d35)

index 方法不存在。让我们新增 index 方法：

```
public function index()
{
    return view('admin/article/index')->withArticles(Article::all());
}
```

#### 新建视图
上面我们已经新建过视图，现在应该已经轻车熟路了。在 `learnlaravel5/resources/views/admin` 下新建 article 文件夹，在文件夹内新建一个 index.blade.php 文件，内容如下：

```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">文章管理</div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            {!! implode('<br>', $errors->all()) !!}
                        </div>
                    @endif

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
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

刷新，错误又变了：

![](https://camo.githubusercontent.com/d610d8470d6f8babbc516537f25743217a792d30/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333135363730353035302e6a70673f743d30)

Article 类不存在？原因很简单：Article 类和当前控制器类不在一个命名空间路径下，不能直接调用。解决办法就是主动导入 `\App\Article` 类：

```
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Article;

class ArticleController extends Controller
{
....
```

如果你还不熟悉命名空间，请阅读[《PHP 命名空间 解惑》](https://lvwenhan.com/php/401.html)。

#### 检查成果
再次刷新，你应该能看到如下画面：

![](https://camo.githubusercontent.com/fc17f69a54a49b54bd5f64cc6de34f47c65e2248/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31302d31353130333135383433363434312e6a7067)

如果你没到这个画面也不用担心，根据错误提示去 Google 吧，一定能解决的。

#### 新增、编辑、删除功能怎么办？
这三个功能我将在下一篇教程与大家分享，这是 2015 版 Laravel 教程做的不够好的地方，其实这里才是最应该掰开揉碎仔细讲解的地方。

> ### 下一步： [2017 版 Laravel 系列入门教程（四）【最适合中国人的 Laravel 教程】](https://github.com/johnlui/Learn-Laravel-5/issues/19)

