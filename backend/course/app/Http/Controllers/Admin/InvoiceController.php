<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function invoice()
    {
        return view('admin.invoice');
    }
}
