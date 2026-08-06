<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified CMS page.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $slug = $request->route('slug');
        $page = CmsPage::where('slug', $slug)->where('status', 'Active')->firstOrFail();

        return view('customer.pages.show', compact('page'));
    }
}
