<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Comment;

use Redirect, Input;

class CommentsController extends Controller {

	public function index()
	{
		return view('admin.comments.index')->withComments(Comment::all());
	}

	public function edit($id)
	{
		return view('admin.comments.edit')->withComment(Comment::find($id));
	}

	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'nickname' => 'required',
			'content' => 'required',
		]);
		if (Comment::where('id', $id)->update(Input::except(['_method', '_token']))) {
			return Redirect::to('admin/comments');
		} else {
			return Redirect::back()->withInput()->withErrors('更新失败！');
		}
	}

	public function destroy($id)
	{
		$comment = Comment::find($id);
		$comment->delete();

		return Redirect::to('admin/comments');
	}

}
