本篇文章中，我将跟大家一起实现 Article 的新增、编辑和删除功能，仔细解读每一段代码，相信本篇文章看完，你就能够 get Laravel 使用之道。

## RESTful 资源控制器
资源控制器是 Laravel 内部的一种功能强大的约定，它约定了一系列对某一种资源进行“增删改查”操作的路由配置，让我们不再需要对每一项需要管理的资源都写 N 行重复形式的路由。中文文档见：https://d.laravel-china.org/docs/5.5/controllers#resource-controllers

我们只需要写一行简单的路由：

```
Route::resource('photo', 'PhotoController');
```

就可以得到下面 7 条路由配置：

![](https://camo.githubusercontent.com/57f463311031716621afe049a8fcc62989279707/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333732343432323536382e6a70673f743d32)

左边是 HTTP 方法，中间是 URL 路径，右边是 控制器中对应的函数，只要某个请求符合这七行中某一行的要求，那么这条请求就会触发第三列的 function。这是 Laravel 对于 RESTful 的规范，它不仅仅帮我们省去了几行路由配置代码，更是如何合理规划 URL 的指路明灯，相信你会从中学到很多。

下面我们正式开始一项一项地实现 Article 的新增、编辑、删除功能：

## 开始行动
### 配置资源路由
将当前路由配置中的 `Route::get('article', 'ArticleController@index');` 改成 `Route::resource('articles', 'ArticleController');`，哦了。注意，article 单数变成了复数。

#### 修改之前写好的视图文件
由于从单数变成了复数，后台首页及文章列表页的视图文件里的链接也需要修改。

1. 修改 `learnlaravel5/resources/views/admin/home.blade.php` 中的 `{ { url('admin/article') }}` 为 `{ { url('admin/articles') }}`。
2. 修改 `learnlaravel5/resources/views/admin/article/index.blade.php` 中的 `{ { url('admin/article/create') }}` 为 `{ { url('admin/articles/create') }}`；修改 `{ { url('admin/article/'.$article->id.'/edit') }}` 为 `{ { url('admin/articles/'.$article->id.'/edit') }}`；修改 `{ { url('admin/article/'.$article->id) }}` 为 `{ { url('admin/articles/'.$article->id) }}`。

### 新增 Article
新增一篇文章需要两个动作：第一步，获取“新增Article”的页面；第二步，提交数据到后端，插入一篇文章到数据库。我们使用下图中红框内的两条路由规则来实现这两步操作：

![](https://camo.githubusercontent.com/b746f92a6f4cd2ce8fcaf9192977f6083174a546/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333732363533323231332e6a70673f743d31)

#### 获取“新增Article”的页面
第一个红框里告诉我们应该使用 `/admin/articles/create` 对应“新增Article”的页面，浏览器使用 GET 方法从服务器获取，对应的是 `ArticleController` 中的 `create()` 方法，下面我们手动新建这个方法：

```
public function create()
{
    return view('admin/article/create');
}
```

新增视图文件 `learnlaravel5/resources/views/admin/article/create.blade.php`：

```
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">新增一篇文章</div>
                <div class="panel-body">

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>新增失败</strong> 输入不符合要求<br><br>
                            {!! implode('<br>', $errors->all()) !!}
                        </div>
                    @endif

                    <form action="{ { url('admin/articles') }}" method="POST">
                        {!! csrf_field() !!}
                        <input type="text" name="title" class="form-control" required="required" placeholder="请输入标题">
                        <br>
                        <textarea name="body" rows="10" class="form-control" required="required" placeholder="请输入内容"></textarea>
                        <br>
                        <button class="btn btn-lg btn-info">新增文章</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

点击文章管理页面最上面的“新增”按钮，你将得到以下页面：

![](https://camo.githubusercontent.com/229fb5764ae1b8b1624fedbf2e33387ce6e4a47f/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333733333039373439382e6a7067)

#### 视图调用
上文中我使用 `return view('admin/article/create');` 返回了视图文件。

`view()` 方法是 Laravel 中一个全局的方法，用于调用视图文件，他接受一个字符串参数，并会按照这个参数去调取对应的路由，这很容易理解。实际上 `'admin/article/create'` 跟 `'admin.article.create'` 是等价的，而且看起来后者更加优雅，不过我个人更推荐前者。代码优雅是好事儿，不过本质上代码是写给人看的，一切提高代码理解成本的行为都是不可取的。

### 提交数据到后端
“新增Article”的页面已经展示出来，下一步就是提交数据到后端了，理解提交数据，要从 HTML 表单开始。

#### 表单
视图文件中有一个表单：

```
<form action="{ { url('admin/articles') }}" method="POST">
    {!! csrf_field() !!}
    <input type="text" name="title" class="form-control" required="required" placeholder="请输入标题">
    <br>
    <textarea name="body" rows="10" class="form-control" required="required" placeholder="请输入内容"></textarea>
    <br>
    <button class="btn btn-lg btn-info">新增文章</button>
</form>
```

这是一个非常普通的 HTML form(表单)，只有两点需要我们费点心思去理解。

第一，表单的 action。form 是 HTML 规范，在点击了表单中的提交按钮后，浏览器会使用 method 属性的值（GET、POST等）将某些数据组装好发送给 action 的值（URL），这里我们动态生成了一个 URL 作为 action，并且指定了表单提交需要使用 POST 方法。

第二，csrf_field。这是 Laravel 中内置的应对 CSRF 攻击的防范措施，任何 POST PUT PATCH 请求都会被检测是否提交了 CSRF 字段。对应的代码为 `learnlaravel5/app/Http/Kernel.php` 里的 `$middlewareGroups` 属性里的 `\App\Http\Middleware\VerifyCsrfToken::class` 值。

`{!! csrf_field() !!}` 实际上会生成一个隐藏的 input：`<input type="hidden" name="_token" value="GYZ8OHDAbZICMcEvcTiS82qlZs2XrELklpEl159S">`

这一行也可以这么写：

```
<input type="hidden" name="_token" value="{ { csrf_token() }}">
```

如果你的系统有很多的 Ajax，而你又不想降低安全性，这里的 csrf_token() 函数将会给你巨大的帮助。

#### 后端接收数据
我们在页面中随便填入一些数据，点击提交按钮，这条请求会被分配到那里呢？

![](https://camo.githubusercontent.com/b746f92a6f4cd2ce8fcaf9192977f6083174a546/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333732363533323231332e6a70673f743d31)

第二个红框告诉我们，应该向 `admin/articles` 以 POST 方法提交表单，其对应的是 `store()` 方法。现在我们新建 store 方法：

```
public function store(Request $request)
{
    $this->validate($request, [
        'title' => 'required|unique:articles|max:255',
        'body' => 'required',
    ]);

    $article = new Article;
    $article->title = $request->get('title');
    $article->body = $request->get('body');
    $article->user_id = $request->user()->id;

    if ($article->save()) {
        return redirect('admin/articles');
    } else {
        return redirect()->back()->withInput()->withErrors('保存失败！');
    }
}
```

#### 检验成果
填入数据：

![](https://camo.githubusercontent.com/5cd99baa6d0af58babecacace279415c3e7a98a7/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333734313339313135382e6a70673f743d31)

点击按钮，页面跳转到“文章管理”页，将此页面拉到最底部：

![](https://camo.githubusercontent.com/6dd2504b32c42dd882489cc4abd9a23ab98f7039/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333734333037353036392e6a7067)

恭喜你，文章新增功能完成！

#### 详细注释
下面我已注释的形式细细解析每一段代码的作用：

```
public function store(Request $request) // Laravel 的依赖注入系统会自动初始化我们需要的 Request 类
{
    // 数据验证
    $this->validate($request, [
        'title' => 'required|unique:articles|max:255', // 必填、在 articles 表中唯一、最大长度 255
        'body' => 'required', // 必填
    ]);

    // 通过 Article Model 插入一条数据进 articles 表
    $article = new Article; // 初始化 Article 对象
    $article->title = $request->get('title'); // 将 POST 提交过了的 title 字段的值赋给 article 的 title 属性
    $article->body = $request->get('body'); // 同上
    $article->user_id = $request->user()->id; // 获取当前 Auth 系统中注册的用户，并将其 id 赋给 article 的 user_id 属性

    // 将数据保存到数据库，通过判断保存结果，控制页面进行不同跳转
    if ($article->save()) {
        return redirect('admin/articles'); // 保存成功，跳转到 文章管理 页
    } else {
        // 保存失败，跳回来路页面，保留用户的输入，并给出提示
        return redirect()->back()->withInput()->withErrors('保存失败！');
    }
}
```

### 编辑 Article
这两行路由配置可以满足我们的需求：

![](https://camo.githubusercontent.com/bb3287fe21c74aae3f8fe8a0e0f2080ea0ca268b/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333830363539313030362e6a70673f743d31)

上面一行：展示“编辑某一篇文章”的表单；下面一行：上传数据并到数据库更新这篇文章。

这个就当做第二个小作业留给你们，尝试自己去构建吧~这里面还有个小坑，参考我的代码就可以迅速地解决呦~

### 删除 Article
删除某个资源跟新增、编辑相比最大的不同就是运行方式的不同：删除按钮看起来是一个独立的按钮，其实它是一个完整的表单，只不过只有这一个按钮暴露在页面上：

```
<form action="{ { url('admin/articles/'.$article->id) }}" method="POST" style="display: inline;">
    { { method_field('DELETE') }}
    { { csrf_field() }}
    <button type="submit" class="btn btn-danger">删除</button>
</form>
```

大家可能注意到了这句代码 `{ { method_field('DELETE') }}`，这是什么意思呢？这是 Laravel 特有的请求处理系统的特殊约定。虽然 DELETE 方法在 [RFC2616](http://www.ietf.org/rfc/rfc2616.txt) 中是可以携带 body 的（甚至 GET 方法都是可以携带的），但是由于历史的原因，不少 web server 软件都将 DELETE 方法和 GET 方法视作不可携带 body 的方法，有些 web server 软件会丢弃 body，有些干脆直接认为请求不合法拒绝接收。所以在这里，Laravel 的请求处理系统要求所有非 GET 和 POST 的请求全部通过 POST 请求来执行，再将真正的方法使用 _method 表单字段携带给后端。上面小作业中的小坑便是这个，PUT/PATCH 请求也要通过 POST 来执行。

在控制器中增加删除文章对应的是 destroy 方法：

```
public function destroy($id)
{
    Article::find($id)->delete();
    return redirect()->back()->withInput()->withErrors('删除成功！');
}
```

点击删除按钮，检验效果：

![](https://camo.githubusercontent.com/95024dbf84cabfbc35ac176df731b58bdd8c4db7/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d31312d31353130333832323639363336332e6a7067)

恭喜你，文章新增、编辑、删除功能构建成功！

> ### 下一步：[2017 版 Laravel 系列入门教程（五）【最适合中国人的 Laravel 教程】](https://github.com/johnlui/Learn-Laravel-5/issues/20)

