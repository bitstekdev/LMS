<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Bootcamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BootcampController extends Controller
{
    public function index(Request $request)
    {
        $query = Bootcamp::query()
            ->with(['user:id,name,email,photo', 'category:id,slug'])
            ->where('status', 1);

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%'.$request->query('search').'%');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        $page_data['bootcamps'] = $query->latest('id')->paginate(9)->appends($request->query());

        return view(theme_path().'bootcamp.index', $page_data);
    }

    public function show($slug)
    {
        $bootcamp = Bootcamp::with(['modules', 'user', 'category'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (! $bootcamp) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $page_data['bootcamp_details'] = $bootcamp;
        $page_data['modules'] = $bootcamp->modules;

        return view(theme_path().'bootcamp.details', $page_data);
    }
}
