> #### 本教程示例代码见：https://github.com/johnlui/Learn-Laravel-5
> 在任何地方卡住，最快的办法就是去看示例代码。

热烈庆祝 Laravel 5.5 LTS 发布！ 实际上 Laravel 上一个 LTS 选择 5.1 是非常不明智的，因为 5.2 增加了许许多多优秀的特性。现在好了，大家都用最新的长期支持版本 5.5 吧！

#### Laravel 5.5 中文文档：https://d.laravel-china.org/docs/5.5
## 默认条件
你应该懂得 PHP 网站运行的基础知识，并且拥有一个完善的开发环境。跟随本教程走完一遍，你将会得到一个基础的包含登录、后台编辑、前台评论的简单 blog 系统。

### Tips
1. 环境要求：PHP 7.0+，MySQL 5.1+
2. 本教程不推荐完全不懂 PHP 与 MVC 编程的人学习，Laravel 的学习曲线不仅仅是陡峭，而且耗时很长，请先做好心理准备。
3. 这不是 “一步一步跟我做” 教程。本教程需要你付出一定的心智去解决一些或大或小的隐藏任务，以达到真正理解 Laravel 运行逻辑的目的。
4. 我使用 Safari 截图是为了好看，你们在开发时记得选择 Chrome 哦~

## 开始学习
### 1. 安装
许多人被拦在了学习 Laravel 的第一步：安装。并不是因为安装有多复杂，而是因为【众所周知的原因】。在此我推荐一个 composer 全量中国镜像：http://pkg.phpcomposer.com 。启用 Composer 镜像服务作为本教程的第一项小作业请自行完成哦。

镜像配置完成后，在终端（Terminal 或 CMD）里切换到你想要放置该网站的目录下（如 C:\wwwroot、/Library/WebServer/Documents/、/var/www/html、/etc/nginx/html 等），运行命令：

```shell
composer create-project laravel/laravel learnlaravel5 ^5.5
```

然后，稍等片刻，当前目录下就会出现一个叫 learnlaravel5 的文件夹，安装完成啦~

### 2. 运行
为了尽可能地减缓学习曲线，推荐大家使用 PHP 内置 web server 驱动我们的网站。运行以下命令：

```shell
cd learnlaravel5/public
php -S 0.0.0.0:1024
```

这时候访问 `http://127.0.0.1:1024` 就是这个样子的：

![](https://camo.githubusercontent.com/07d7087f77700bd9330ad493be937f9e02cc92e0/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034363031303136353339382e6a70673f743d32)

我在本地 hosts 中绑定了 fuck.io 到 127.0.0.1，所以截图中我的域名是 fuck.io 而不是 127.0.0.1，其实他们是完全等价的。跟过去几年比，有一件大快人心的大好事，`fonts.googleapis.com` 网站已经转移到了墙内，不会再“白屏”了。

至于为什么选择 1024 端口？因为他是 *UNIX 系统动态端口的开始，是我们不需要 root 权限就可以随意监听的数值最小的端口。

另外，如果你不熟悉 PHP 运行环境搭建的话不要轻易尝试使用 Apache 或 Nginx 驱动 Laravel，特别是在开启了 SELinux 的 Linux 系统上跑这俩货。关于 Laravel 在 Linux 上部署的大坑，我可能要单写一篇长文分享给宝宝们。（说了一年了也没见写呀）

### 3. 体验牛逼闪闪的 Auth 系统
Laravel 利用 PHP5.4 的新特性 [trait](http://php.net/manual/zh/language.oop5.traits.php) 内置了非常完善好用的简单用户登录注册功能，适合一些不需要复杂用户权限管理的系统，例如公司内部用的简单管理系统。

激活这个功能非常容易，运行以下命令：

```
php artisan make:auth
```

访问 `http://fuck.io:1024/login`，看到以下页面：

![](https://camo.githubusercontent.com/a9d216fd47dcbb1bd3729fa5ecf38106f5f1ff7b/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034363031363333383633382e6a70673f743d31)

### 4. 连接数据库
接下来我们要连接数据库了，请自行准备好 MySQL 服务哦。

#### a. 修改配置
不出意外的话，learnlaravel5 目录下已经有了一个 .env 文件，如果没有，可以复制一份 .env.example 文件重命名成 .env，修改下面几行的值：

```shell
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel5
DB_USERNAME=root
DB_PASSWORD=password
```

推荐新建一个名为 laravel5 的数据库（编码设置为 utf8mb4），并且使用 root 账户直接操作，降低学习数据库的成本。

数据库配置好之后，在登录界面填写任意邮箱和密码，点击 Login，你应该会得到以下画面：

![](https://camo.githubusercontent.com/3373a697bd90199d76b632d1a0cc94ab427b0e39/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034363033373238383339322e6a70673f743d31)

它说 users 表不存在，接下来我们将见识 Laravel 另外一个实用特性。

> tip：如果你下载了我的示例代码，默认是跑不起来的，还需要生成 .env，运行 composer update 填充 vendor 文件夹等操作，如果部署在 Linux 上，用的 Apache 或 Nginx，还需要设置文件夹权限：`chmod 777 -R storage bootstrap/cache`

#### b. 进行数据库迁移（migration）
运行命令：

```shell
php artisan migrate
```

我们得到了如下结果：

```shell
» php artisan migrate
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table
```

数据库迁移成功！赶快打开 http://fuck.io:1024/home 点击右上角的注册按钮注册一个用户试试吧~

下图是本宝宝注册了一个 username 为 1 用户：

![](https://camo.githubusercontent.com/0289ae8e12890e81f314479f314fe459ffb5d592/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034363034303832383433312e6a7067)

#### c. migration 是啥？
打开 `learnlaravel5/database/migrations/2014_10_12_000000_create_users_table.php` 文件，你肯定能一眼看出它的作用：用 PHP 描述数据库构造，并且使用命令行一次性部署所有数据库结构。

### 5. 使用 Laravel 的“葵花宝典”：Eloquent
Eloquent 是 Laravel 的 ORM，是 Laravel 系统中最强大的地方，没有之一。当初 Laravel 作者在开发第一版的时候花了整整三分之一的时间才搞出来 Eloquent。当然，“欲练此功，必先自宫”，Eloquent 也是 Laravel 中最慢的地方，迄今无法解决。（路由、自动载入、配置分散、视图引发的性能问题都通过缓存几乎彻底解决了，Composer Autoload 巨量的性能消耗也被 PHP7 手起刀落解决了）

当然，我们还是要承袭第一版教程中对 Eloquent ORM 的描述：鹅妹子英！

> 如果你想深入地了解 Eloquent，可以阅读系列文章：[深入理解 Laravel Eloquent（一）——基本概念及用法](https://lvwenhan.com/laravel/421.html)

#### a. Eloquent 是什么
Eloquent 是 Laravel 内置的 ORM 系统，我们的 Model 类将继承自 Eloquent 提供的 Model 类，然后，就天生具备了数十个异常强大的函数，从此想干啥事儿都是一行代码就搞定。

经过了三年多的大规模使用，我发现 Eloquent 另辟蹊径采用和 Java 技术完全不同的思路解决了多人开发耦合过重的问题：数据库相关操作全部用一句话解决，Model 中不写共用函数，大幅降低了 bug 几率。什么？你说性能？软件工程研究的对象是人，性能的优先级比代码格式规范都低好嘛。我时刻谨记：代码是写给人看的，只是恰好能运行。

#### b. 怎么用？
我们使用 Artisan 工具新建 Model 类及其附属的 Migration 和 Seeder（数据填充）类。

运行以下命令：

```shell
php artisan make:model Article
```

去看看你的 app 目录，下面是不是多了一个 Article.php 文件？那就是 Artisan 帮我们生成的 Model 文件：

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
}
```

如此简洁有力的代码，隐藏了背后极高的难度和巨大的复杂度，让我们闭上眼睛，静静地感受 Laravel 的优雅吧 (～￣▽￣)～

### 下面是几个简单的例子：
#### 找到 id 为 2 的文章打印其标题
```
$article = Article::find(2);

echo $article->title;
```

#### 查找标题为“我是标题”的文章，并打印 id
```
$article = Article::where('title', '我是标题')->first();

echo $article->id;
```

#### 查询出所有文章并循环打印出所有标题
```
$articles = Article::all(); // 此处得到的 $articles 是一个对象集合，可以在后面加上 '->toArray()' 变成多维数组。

foreach ($articles as $article) {

    echo $article->title;

}
```

#### 查找 id 在 10~20 之间的所有文章并打印所有标题
```
$articles = Article::where('id', '>', 10)->where('id', '<', 20)->get();

foreach ($articles as $article) {

    echo $article->title;

}
```

#### 查询出所有文章并循环打印出所有标题，按照 updated_at 倒序排序
```
$articles = Article::where('id', '>', 10)->where('id', '<', 20)->orderBy('updated_at', 'desc')->get();

foreach ($articles as $article) {

    echo $article->title;

}
```

### 6. 使用 Migration 和 Seeder
接下来我们生成对应 Article 这个 Model 的 Migration 和 Seeder。

#### a. 使用 artisan 生成 Migration
在 learnlaravel5 目录下运行命令：

```shell
php artisan make:migration create_articles_table
```

成功之后打开 `learnlaravel5/database/migrations`，你会发现有一个名为 2*****_create_articles_table 的文件被创建了。我们修改他的 up 函数为：

```
public function up()
{
    Schema::create('articles', function (Blueprint $table)
    {
        $table->increments('id');
        $table->string('title');
        $table->text('body')->nullable();
        $table->integer('user_id');
        $table->timestamps();
    });
}
```

这几行代码描述的是 Article 对应的数据库中那张表的结构。Laravel Model 默认的表名是这个英文单词的复数形式，在这里，就是 articles。接下来让我们把 PHP 代码变成真实的 MySQL 中的数据表，运行命令：

```shell
php artisan migrate
```

执行成功后，articles 表已经出现在数据库里了：

![](https://camo.githubusercontent.com/5a5c552f1195a9251cd6b525c6a27ff78a288ba0/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034373031393633343333322e6a7067)

上图中的软件叫 Sequel Pro，是一个开源的 MAC 下 MySQL GUI 管理工具，超好用，推荐给用 MAC 的同学。

#### b. 使用 artisan 生成 Seeder
Seeder 是我们接触到的一个新概念，字面意思为播种机。Seeder 解决的是我们在开发 web 应用的时候，需要手动向数据库中填入假数据的繁琐低效问题。

运行以下命令创建 Seeder 文件：

```shell
php artisan make:seeder ArticleSeeder
```

我们会发现 `learnlaravel5/database/seeds` 里多了一个文件 `ArticleSeeder.php`，修改此文件中的 run 函数为：

```
public function run()
{
    DB::table('articles')->delete();

    for ($i=0; $i < 10; $i++) {
        \App\Article::create([
            'title'   => 'Title '.$i,
            'body'    => 'Body '.$i,
            'user_id' => 1,
        ]);
    }
}
```

上面代码中的 `\App\Article` 为命名空间绝对引用。如果你对命名空间还不熟悉，可以读一下 [《PHP 命名空间 解惑》](https://lvwenhan.com/php/401.html)，很容易理解的。

接下来我们把 ArticleSeeder 注册到系统内。修改 `learnlaravel5/database/seeds/DatabaseSeeder.php` 中的 run 函数为：

```
public function run()
{
    $this->call(ArticleSeeder::class);
}
```

由于 database 目录没有像 app 目录那样被 composer 注册为 psr-4 自动加载，采用的是 [psr-0 classmap 方式](https://github.com/johnlui/Learn-Laravel-5/blob/laravel5.2/composer.json#L19-L21)，所以我们还需要运行以下命令把 `ArticleSeeder.php` 加入自动加载系统，避免找不到类的错误：

```shell
composer dump-autoload
```

然后执行 seed：

```shell
php artisan db:seed
```

你应该得到如下结果：

```
» php artisan db:seed
Seeding: ArticleSeeder
```

这时候刷新一下数据库中的 articles 表，会发现已经被插入了 10 行假数据：

![](https://camo.githubusercontent.com/d90595c15cd86dba97b53e9021d2d2df638dec1e/687474703a2f2f716e2e6c7677656e68616e2e636f6d2f323031372d30392d30362d31353034373031393136393935352e6a70673f743d34)

> ### 下一步：[2017 版 Laravel 系列入门教程（二）【最适合中国人的 Laravel 教程】](https://github.com/johnlui/Learn-Laravel-5/issues/17)

