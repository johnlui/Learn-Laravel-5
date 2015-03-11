<?php namespace App\Http\Controllers;

use App\Page;

class HomeController extends Controller {

	public function index()
	{
		return view('home')->withPages(Page::all());
	}

}
