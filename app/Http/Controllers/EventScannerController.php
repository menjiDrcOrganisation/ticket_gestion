<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventScannerController extends Controller
{
public function index()
    {
        return view('event_scanner.index');
    }
}
