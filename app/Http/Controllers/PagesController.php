<?php namespace App\Http\Controllers;

use App\Page;

class PagesController extends Controller {

  public function __construct()
  {
    $this->middleware('guest');
  }

  public function show($id)
  {
    return view('pages.show')->withPage(Page::find($id));
  }

}
