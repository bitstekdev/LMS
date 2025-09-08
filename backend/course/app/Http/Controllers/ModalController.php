<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ModalController extends Controller
{
    /**
     * Render a dynamic view for modal with optional data from request.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function common_view_function(Request $request, string $view_path = '')
    {
        if (! View::exists($view_path)) {
            abort(404, "Modal view [$view_path] not found.");
        }

        $page_data = $request->all();

        return view($view_path, $page_data);
    }
}
