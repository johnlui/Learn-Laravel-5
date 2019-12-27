本篇文章中，我将跟大家一起体验 Laravel 框架最重要的部分——路由系统。

如果你读过 2015 版的教程，你会发现那篇文章里大书特书的 Auth 系统构建已经被 Laravel 捎带手给解决了。在更早的 2014（Laravel 4）版教程中，实际上我是通过让大家自己手动构建高难度的 Auth 系统来提高短期学习曲线的斜率，以便大家能更快地感受到 Laravel 运行的原理。但是很遗憾，现在的 Auth 系统实在是太强大了，执行几句命令就激活了这个功能，新手其实还是云里雾里。为了弥补这个缺憾，我决定赤膊上阵，手刃路由系统，直接给大家展示 Laravel 是如何组织 MVC 架构而控制网站运行的。

## 初识路由
路由系统是所有 PHP 框架的核心，路由承载的是 URL 到代码片段的映射，不同的框架所附带的路由系统是这个框架本质最真实的写照，一丝不挂，一览无余。Laravel 路由中文文档：http://laravel-china.org/docs/5.5/routing

Laravel 5.3 之后就把路由放到了 `learnlaravel5/routes` 文件夹中，一共有四个文件。

我们先看一下`web.php`中仅存的几行代码：

```
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
```

中间的一行代码 `Auth::routes();` 就是 Auth 系统自动注入的路由配置，我们不用深究，我们的注意力主要集中头三行和最后一行代码上。

## 命名空间
我一直认为 Laravel 5 除了性能大幅提升之外相对于 4 最大的进步就在于新的命名空间规划：更清晰，更合理，更有利于新手。

### Laravel 4 失败的简洁
Laravel 4 时代，大量的代码都运行在根命名空间下，路由、Controller、Model 等等。看起来这么做可以少写几行枯燥的 `use xxxx;`，实则是对于命名空间的误使用，而且对于新手学习命名空间是有毒的。

### 绝对类名
Laravel 5 全面引入了 psr-4 命名空间标准：命名空间和实际文件所在的文件夹层级一致，文件夹首字母大写，并自动成为此文件的约定命名空间。举个小栗子：`learnlaravel5/app/Http/Controllers/HomeController.php` 的绝对类名为：`\App\Http\Controllers\HomeController`，`learnlaravel5/app/User.php` 的绝对类名为：`\App\User`。（实际上 psr-4 是自动加载标准，用在这里故称其为命名空间标准。）

“绝对类名”是我自创的：在启用了命名空间的系统中，子命名空间下的类有一个全局都可以直接访问的名称，这个名称就是该类的命名空间全称。虽然命名空间在“实用主义”的 PHP 语言里看起来十分古怪，不过他也还是 PHP 嘛，依然遵循 PHP 的运行原理和哲学。同理，Laravel 无论多么强大，他都是 PHP 代码写成的，所以当你苦于 Laravel 没有提供某个你需要的功能时，不要惊慌不要着急，just write your PHP code。

> psr-4 的官方英文文档在这里：http://www.php-fig.org/psr/psr-4/

### 好用的资料
命名空间其实没什么特别难的地方，我曾经写过一篇文章专门扒光命名空间的秘密：[《PHP 命名空间 解惑 》](https://lvwenhan.com/php/401.html)

## 基础路由解析
### 闭包路由
路由文件中前三行即为闭包路由：

```
Route::get('/', function () {
    return view('welcome');
});
```

闭包路由使用闭包作为此条请求的响应代码，方便灵活，很多简单操作直接在闭包里解决即可。例如“输出服务器当前时间”：

```
Route::get('now', function () {
    return date("Y-m-d H:i:s");
});
```

如果你想得到北京时间，请在 `learnlaravel5/config/app.php` 第 68 行左右把 timezone 设置为上海：

```
'timezone' => 'Asia/Shanghai',
```

这时候访问 http://fuck.io:1024/now 可以得到如下结果：

![](https://camo.githubusercontent.com/69889b2255939c619864a22ade772bf2d079488e/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d30382d31353130313330333332313535352e6a7067)

### 控制器@方法 路由
闭包路由虽然灵活强大，不过大多数场景下我们还是需要回归到 MVC 架构的：

```
Route::get('/home', 'HomeController@index')->name('home');
```

这行路由代码的意思想必大家都能猜到一二了：当以 GET 方法访问 `http://fuck.io:1024/home` 的时候，调用 HomeController 控制器中的 index 方法（函数）。同理，你可以使用 `Route::post('/home', 'HomeController@indexPost');` 响应 POST 方法的请求。最后的`->name()`不是必须的，感兴趣可以自己了解。

### 控制器@方法 调用原理浅析
Laravel 的路由跟所有 PHP 框架的路由一样，都是用的最简单直接的 PHP 方式来调用控制器中的方法的：使用字符串初始化类得到对象，调用对象的指定方法，返回结果。下面我简单罗列几步对 Laravel 路由调用过程的探测，感兴趣的话可以自己研究。

#### `learnlaravel5/app/Providers/RouteServiceProvider.php`
全局搜索 `routes.php`，我们找到了这个文件。此文件最后的 mapWebRoutes 方法，给所有的路由统一加进了一个路由组，定义了一个命名空间和一个中间件：

```
protected function mapWebRoutes()
{
    Route::middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/web.php'));
}
```

顺着这个函数往上看，你会发现命名空间定义的地方：

```
protected $namespace = 'App\Http\Controllers';
```

之后命名空间、类、方法是如何传递的呢？

#### `learnlaravel5/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php`
经过简单的追踪，我们找到了这个文件。让我们在 dispatch 方法中增加一行 `var_dump($controller);`，刷新`http://fuck.io:1024/home`就可以看到页面上如下的输出：

![](https://camo.githubusercontent.com/0132db867ee307bc09dee6684f85529ddad17e55/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d30382d31353130313330383930323033342e6a7067)

开头的`App\Http\Controllers\HomeController`就是我们要调用的控制器类的“绝对类名”，注意这里是不带`\`根命名空间符号的。

#### 最后一步
Laravel 使用了完整的面向对象程序架构，对控制器的调用进行了超多层封装，所以最简单地探测方式其实是手动抛出错误，这样就可以看到完整的调用栈：

在 HomeController 的 index 方法里的 return 之前增加一行 `throw new \Exception("我故意的", 1);`，刷新页面，你将看到以下画面：

![](https://camo.githubusercontent.com/60d2333ed1a21c3d687cec319462fb9af8ff4dd6/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d31312d30382d31353130313331303034383433362e6a70673f743d32)

我们可以看到，是 `learnlaravel5/vendor/laravel/framework/src/Illuminate/Routing/Controller.php` 第 54 行最终驱动起了 HomeController：

```
public function callAction($method, $parameters)
{
    return call_user_func_array([$this, $method], $parameters);
}
```

具体的细节不再详解，大家如果感兴趣的话，把这些方法一个一个地都看一遍吧，相信对于你理解 Laravel 运行原理很有帮助。其实 PHP 跟字符串结合的紧密程度已经紧逼 js 和 JSON 了。结尾分享一个小彩蛋：[这个laravel路由怎么写?](https://www.zhihu.com/question/31330386/answer/51544599)

> ### 下一步：[2017 版 Laravel 系列入门教程（三）【最适合中国人的 Laravel 教程】](https://github.com/johnlui/Learn-Laravel-5/issues/18)

