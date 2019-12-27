本文是本系列教程的完结篇，我们将一起给 Article 加入评论功能，让游客在前台页面可以查看、提交、回复评论，并完成后台评论管理功能，可以删除、编辑评论。Article 和评论将使用 Laravel Eloquent 提供的“一对多关系”功能大大简化模型间关系的复杂度。最终，我们将得到一个个人博客系统的雏形，并布置一个大作业，供大家实战练习。

本篇文章中我将会使用一些 Laravel 的高级功能，这些高级功能对新手理解系统是不利的，但熟手使用这些功能可以大幅提升开发效率。

## 回顾 Eloquent
前面我们已经说过，Laravel Eloquent ORM 是 Laravel 中最强大的部分，也是 Laravel 能如此流行最重要的原因。中文文档在：https://d.laravel-china.org/docs/5.5/eloquent

`learnlaravel5/app/Article.php` 就是一个最简单的 Eloquent Model 类：

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
}
```

若想进一步了解 Eloquent 推荐阅读系列文章：[深入理解 Laravel Eloquent](https://lvwenhan.com/laravel/421.html)

## 构建评论系统
### 基础规划
我们需要新建一个表专门用来存放每一条评论，每一条评论都属于某一篇文章。评论之间的层级关系比较复杂，本文为入门教程，主要是为了带领大家体验模型间关系，就不再做过多的规划了，将“回复别人的评论”暂定为简单的在评论内容前面增加 @johnlui 这样的字符串。

### 建立 Model 类和数据表
创建名为 Comment 的 Model 类，并顺便创建附带的 migration，在 learnlaravel5 目录下运行命令：

```shell
php artisan make:model Comment -m
```

这样一次性建立了 Comment 类和 `learnlaravel5/database/migrations/2017_11_11_151823_create_comments_table.php` 两个文件。填充该文件的 up 方法，给 `comments` 表增加字段：

```
public function up()
{
    Schema::create('comments', function (Blueprint $table) {
        $table->increments('id');
        $table->string('nickname');
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('content')->nullable();
        $table->integer('article_id');
        $table->timestamps();
    });
}
```

之后运行命令：

```shell
php artisan migrate
```

去数据库里瞧瞧，comments 表已经躺在那儿啦。

### 建立“一对多关系”
在 Article 模型中增加一对多关系的函数：

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public function hasManyComments()
    {
        return $this->hasMany('App\Comment', 'article_id', 'id');
    }
}
```

搞定啦！Eloquent 中模型间关系就是这么简单！

模型间关系中文文档：http://laravel-china.org/docs/5.1/eloquent-relationships
扩展阅读：[深入理解 Laravel Eloquent（三）——模型间关系（关联）](https://lvwenhan.com/laravel/423.html)

### 构建前台 UI
让我们修改前台的视图文件，想办法把评论功能加进去。

#### 创建前台的 ArticleController 类
运行命令：

```shell
php artisan make:controller ArticleController
```

增加路由：

```
Route::get('article/{id}', 'ArticleController@show');
```

此处的 {id} 指代任意字符串，在我们的规划中，此字段为文章 ID，为数字，但是本行路由却会尝试匹配所有请求，所以当你遇到了奇怪的路由调用的方法跟你想象的不一样时，记得检查路由顺序。路由匹配方式为前置匹配：任何一条路由规则匹配成功，会立刻返回结果，后面的路由便没有了响应的机会。

给 ArticleController 增加 show 函数：

```
public function show($id)
{
    return view('article/show')->withArticle(Article::with('hasManyComments')->find($id));
}
```

别忘了在顶部引入 Model 类，否则会报类找不到的错误：

```
....
use App\Article;

class ArticleController extends Controller
{
....
```

#### 创建前台文章展示视图
新建 `learnlaravel5/resources/views/article/show.blade.php` 文件：

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

    <div id="content" style="padding: 50px;">

        <h4>
            <a href="/"><< 返回首页</a>
        </h4>

        <h1 style="text-align: center; margin-top: 50px;">{{ $article->title }}</h1>
        <hr>
        <div id="date" style="text-align: right;">
            {{ $article->updated_at }}
        </div>
        <div id="content" style="margin: 20px;">
            <p>
                {{ $article->body }}
            </p>
        </div>

        <div id="comments" style="margin-top: 50px;">

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>操作失败</strong> 输入不符合要求<br><br>
                    {!! implode('<br>', $errors->all()) !!}
                </div>
            @endif

            <div id="new">
                <form action="{{ url('comment') }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="article_id" value="{{ $article->id }}">
                    <div class="form-group">
                        <label>Nickname</label>
                        <input type="text" name="nickname" class="form-control" style="width: 300px;" required="required">
                    </div>
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" class="form-control" style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Home page</label>
                        <input type="text" name="website" class="form-control" style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" id="newFormContent" class="form-control" rows="10" required="required"></textarea>
                    </div>
                    <button type="submit" class="btn btn-lg btn-success col-lg-12">Submit</button>
                </form>
            </div>

            <script>
            function reply(a) {
              var nickname = a.parentNode.parentNode.firstChild.nextSibling.getAttribute('data');
              var textArea = document.getElementById('newFormContent');
              textArea.innerHTML = '@'+nickname+' ';
            }
            </script>

            <div class="conmments" style="margin-top: 100px;">
                @foreach ($article->hasManyComments as $comment)

                    <div class="one" style="border-top: solid 20px #efefef; padding: 5px 20px;">
                        <div class="nickname" data="{{ $comment->nickname }}">
                            @if ($comment->website)
                                <a href="{{ $comment->website }}">
                                    <h3>{{ $comment->nickname }}</h3>
                                </a>
                            @else
                                <h3>{{ $comment->nickname }}</h3>
                            @endif
                            <h6>{{ $comment->created_at }}</h6>
                        </div>
                        <div class="content">
                            <p style="padding: 20px;">
                                {{ $comment->content }}
                            </p>
                        </div>
                        <div class="reply" style="text-align: right; padding: 5px;">
                            <a href="#new" onclick="reply(this);">回复</a>
                        </div>
                    </div>

                @endforeach
            </div>
        </div>

    </div>

</body>
</html>
```

### 构建评论存储功能
我们需要创建一个 CommentsController 控制器，并增加一条“存储评论”的路由。运行命令：

```shell
php artisan make:controller CommentController
```

控制器创建成功，接下来我们增加一条路由：

```
Route::post('comment', 'CommentController@store');
```

给这个类增加 store 函数：

```
public function store(Request $request)
{
    if (Comment::create($request->all())) {
        return redirect()->back();
    } else {
        return redirect()->back()->withInput()->withErrors('评论发表失败！');
    }
}
```

此处 Comment 类请自己引入。

#### 批量赋值
我们采用批量赋值方法来减少存储评论的代码，[批量赋值中文文档](https://d.laravel-china.org/docs/5.5/eloquent#mass-assignment)。

给 Comment 类增加 $fillable 成员变量：

```
protected $fillable = ['nickname', 'email', 'website', 'content', 'article_id'];
```

### 检查成果
前台文章展示页：

![](https://camo.githubusercontent.com/7ecafe98d5e9c477d745a90802f592f7f1e69927/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333835343330393235362e6a7067)

提交几条评论之后：

![](https://camo.githubusercontent.com/be0a020f3c437b293f8133d11895924210171cfe/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333835353138303639392e6a7067)

恭喜你，前台评论功能构建完成！

## 【大作业】构建后台评论管理功能
评论跟 Article 一样，是一种可以管理的资源列表。2015 版教程的最后，我风风火火地罗列了一堆又一堆的代码，其实对还没入门的人几乎没用。在此，我将这个功能作为大作业布置给大家。大作业嘛，当然是没有标准答案的，但有效果图：

![](https://camo.githubusercontent.com/eeca8316c3fa044bb2e9d1b47291b6bd96fa0d6b/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031362d30362d30332d31343634393636383832333234322e6a7067)

![](https://camo.githubusercontent.com/1f392d801eeb7e86700f7bf41e2b6459f536506e/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031362d30362d30332d31343634393636383934343330342e6a7067)

在做这个大作业的过程中，你将会反复地回头去看前面的教程，反复地阅读中文文档，会仔细阅读我的代码，等你完成大作业的时候，Laravel 就真正入门啦~~

本文是本系列教程的完结篇，我们将一起给 Article 加入评论功能，让游客在前台页面可以查看、提交、回复评论，并完成后台评论管理功能，可以删除、编辑评论。Article 和评论将使用 Laravel Eloquent 提供的“一对多关系”功能大大简化模型间关系的复杂度。最终，我们将得到一个个人博客系统的雏形，并布置一个大作业，供大家实战练习。

本篇文章中我将会使用一些 Laravel 的高级功能，这些高级功能对新手理解系统是不利的，但熟手使用这些功能可以大幅提升开发效率。

## 回顾 Eloquent
前面我们已经说过，Laravel Eloquent ORM 是 Laravel 中最强大的部分，也是 Laravel 能如此流行最重要的原因。中文文档在：https://d.laravel-china.org/docs/5.5/eloquent

`learnlaravel5/app/Article.php` 就是一个最简单的 Eloquent Model 类：

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
}
```

若想进一步了解 Eloquent 推荐阅读系列文章：[深入理解 Laravel Eloquent](https://lvwenhan.com/laravel/421.html)

## 构建评论系统
### 基础规划
我们需要新建一个表专门用来存放每一条评论，每一条评论都属于某一篇文章。评论之间的层级关系比较复杂，本文为入门教程，主要是为了带领大家体验模型间关系，就不再做过多的规划了，将“回复别人的评论”暂定为简单的在评论内容前面增加 @johnlui 这样的字符串。

### 建立 Model 类和数据表
创建名为 Comment 的 Model 类，并顺便创建附带的 migration，在 learnlaravel5 目录下运行命令：

```shell
php artisan make:model Comment -m
```

这样一次性建立了 Comment 类和 `learnlaravel5/database/migrations/2017_11_11_151823_create_comments_table.php` 两个文件。填充该文件的 up 方法，给 `comments` 表增加字段：

```
public function up()
{
    Schema::create('comments', function (Blueprint $table) {
        $table->increments('id');
        $table->string('nickname');
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('content')->nullable();
        $table->integer('article_id');
        $table->timestamps();
    });
}
```

之后运行命令：

```shell
php artisan migrate
```

去数据库里瞧瞧，comments 表已经躺在那儿啦。

### 建立“一对多关系”
在 Article 模型中增加一对多关系的函数：

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public function hasManyComments()
    {
        return $this->hasMany('App\Comment', 'article_id', 'id');
    }
}
```

搞定啦！Eloquent 中模型间关系就是这么简单！

模型间关系中文文档：http://laravel-china.org/docs/5.1/eloquent-relationships
扩展阅读：[深入理解 Laravel Eloquent（三）——模型间关系（关联）](https://lvwenhan.com/laravel/423.html)

### 构建前台 UI
让我们修改前台的视图文件，想办法把评论功能加进去。

#### 创建前台的 ArticleController 类
运行命令：

```shell
php artisan make:controller ArticleController
```

增加路由：

```
Route::get('article/{id}', 'ArticleController@show');
```

此处的 {id} 指代任意字符串，在我们的规划中，此字段为文章 ID，为数字，但是本行路由却会尝试匹配所有请求，所以当你遇到了奇怪的路由调用的方法跟你想象的不一样时，记得检查路由顺序。路由匹配方式为前置匹配：任何一条路由规则匹配成功，会立刻返回结果，后面的路由便没有了响应的机会。

给 ArticleController 增加 show 函数：

```
public function show($id)
{
    return view('article/show')->withArticle(Article::with('hasManyComments')->find($id));
}
```

别忘了在顶部引入 Model 类，否则会报类找不到的错误：

```
....
use App\Article;

class ArticleController extends Controller
{
....
```

#### 创建前台文章展示视图
新建 `learnlaravel5/resources/views/article/show.blade.php` 文件：

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

    <div id="content" style="padding: 50px;">

        <h4>
            <a href="/"><< 返回首页</a>
        </h4>

        <h1 style="text-align: center; margin-top: 50px;">{{ $article->title }}</h1>
        <hr>
        <div id="date" style="text-align: right;">
            {{ $article->updated_at }}
        </div>
        <div id="content" style="margin: 20px;">
            <p>
                {{ $article->body }}
            </p>
        </div>

        <div id="comments" style="margin-top: 50px;">

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>操作失败</strong> 输入不符合要求<br><br>
                    {!! implode('<br>', $errors->all()) !!}
                </div>
            @endif

            <div id="new">
                <form action="{{ url('comment') }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="article_id" value="{{ $article->id }}">
                    <div class="form-group">
                        <label>Nickname</label>
                        <input type="text" name="nickname" class="form-control" style="width: 300px;" required="required">
                    </div>
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" class="form-control" style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Home page</label>
                        <input type="text" name="website" class="form-control" style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" id="newFormContent" class="form-control" rows="10" required="required"></textarea>
                    </div>
                    <button type="submit" class="btn btn-lg btn-success col-lg-12">Submit</button>
                </form>
            </div>

            <script>
            function reply(a) {
              var nickname = a.parentNode.parentNode.firstChild.nextSibling.getAttribute('data');
              var textArea = document.getElementById('newFormContent');
              textArea.innerHTML = '@'+nickname+' ';
            }
            </script>

            <div class="conmments" style="margin-top: 100px;">
                @foreach ($article->hasManyComments as $comment)

                    <div class="one" style="border-top: solid 20px #efefef; padding: 5px 20px;">
                        <div class="nickname" data="{{ $comment->nickname }}">
                            @if ($comment->website)
                                <a href="{{ $comment->website }}">
                                    <h3>{{ $comment->nickname }}</h3>
                                </a>
                            @else
                                <h3>{{ $comment->nickname }}</h3>
                            @endif
                            <h6>{{ $comment->created_at }}</h6>
                        </div>
                        <div class="content">
                            <p style="padding: 20px;">
                                {{ $comment->content }}
                            </p>
                        </div>
                        <div class="reply" style="text-align: right; padding: 5px;">
                            <a href="#new" onclick="reply(this);">回复</a>
                        </div>
                    </div>

                @endforeach
            </div>
        </div>

    </div>

</body>
</html>
```

### 构建评论存储功能
我们需要创建一个 CommentsController 控制器，并增加一条“存储评论”的路由。运行命令：

```shell
php artisan make:controller CommentController
```

控制器创建成功，接下来我们增加一条路由：

```
Route::post('comment', 'CommentController@store');
```

给这个类增加 store 函数：

```
public function store(Request $request)
{
    if (Comment::create($request->all())) {
        return redirect()->back();
    } else {
        return redirect()->back()->withInput()->withErrors('评论发表失败！');
    }
}
```

此处 Comment 类请自己引入。

#### 批量赋值
我们采用批量赋值方法来减少存储评论的代码，[批量赋值中文文档](https://d.laravel-china.org/docs/5.5/eloquent#mass-assignment)。

给 Comment 类增加 $fillable 成员变量：

```
protected $fillable = ['nickname', 'email', 'website', 'content', 'article_id'];
```

### 检查成果
前台文章展示页：

![](https://camo.githubusercontent.com/7ecafe98d5e9c477d745a90802f592f7f1e69927/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333835343330393235362e6a7067)

提交几条评论之后：

![](https://camo.githubusercontent.com/be0a020f3c437b293f8133d11895924210171cfe/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333835353138303639392e6a7067)

恭喜你，前台评论功能构建完成！

## 【大作业】构建后台评论管理功能
评论跟 Article 一样，是一种可以管理的资源列表。2015 版教程的最后，我风风火火地罗列了一堆又一堆的代码，其实对还没入门的人几乎没用。在此，我将这个功能作为大作业布置给大家。大作业嘛，当然是没有标准答案的，但有效果图：

![](https://camo.githubusercontent.com/eeca8316c3fa044bb2e9d1b47291b6bd96fa0d6b/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031362d30362d30332d31343634393636383832333234322e6a7067)

![](https://camo.githubusercontent.com/1f392d801eeb7e86700f7bf41e2b6459f536506e/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031362d30362d30332d31343634393636383934343330342e6a7067)

在做这个大作业的过程中，你将会反复地回头去看前面的教程，反复地阅读中文文档，会仔细阅读我的代码，等你完成大作业的时候，Laravel 就真正入门啦~~

