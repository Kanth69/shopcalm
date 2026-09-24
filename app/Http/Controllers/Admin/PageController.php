<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;

class PageController extends Controller
{
    private function checkSuperAdmin(): void
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin has permission to manage CMS Pages.');
        }
    }

    public function index()
    {
        $this->checkSuperAdmin();
        $pages = Page::all();
        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page)
    {
        $this->checkSuperAdmin();
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $this->checkSuperAdmin();
        $request->validate([
            'content' => 'required',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $page->update([
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.pages.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Page updated successfully.']);
    }
}
