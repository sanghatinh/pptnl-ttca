<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvestmentProject;

class InvestmentProjectController extends Controller
{
    public function index()
    {
        $projects = InvestmentProject::all();
        return response()->json($projects);
    }
}