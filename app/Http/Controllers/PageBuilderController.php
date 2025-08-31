<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    public function index()
    {
        return view('backEnd.pageBuilder.index');
    }

    public function create(): void
    {
        //
    }

    public function store(Request $request): void
    {
        //
    }

    public function show($id): void
    {
        //
    }

    public function edit($id): void
    {
        //
    }

    public function update(Request $request, $id): void
    {
        //
    }

    public function destroy($id): void
    {
        //
    }
}
