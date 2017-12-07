<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stocks = Stock::all();
        return view('stock.index', compact('stocks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stock.change', ['mode' => 'create']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $stock = new Stock;
        $stock->symbol = strtoupper($request->symbol);
        $stock->name = $request->name;
        $stock->save();

        return redirect()
                ->route('stock.index')
                ->with('success', $request->symbol.' added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $symbol
     * @return \Illuminate\Http\Response
     */
    public function show(string $symbol)
    {
        $stocks = Stock::where('symbol', strtoupper($symbol))->get();
        return view('stock.index', compact('stocks'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $symbol
     * @return \Illuminate\Http\Response
     */
    public function edit(string $symbol)
    {
        return view(
            'stock.change', 
            [
                'mode' => 'edit',
                'stock' => Stock::where('symbol', strtoupper($symbol))->get()->first()
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $symbol
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $symbol)
    {
        $status = Stock::where('symbol', strtoupper($symbol))
                    ->update([
                        'symbol' => strtoupper($request->symbol),
                        'name' => $request->name
                    ]);
        if ($status) {
            // Show update info
            return redirect()->route('stock.index')->with('success', $request->symbol.' updated successfully.');
        } else {
            // Back to edit page
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $symbol
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $symbol)
    {
        $status = Stock::where('symbol', strtoupper($symbol))->delete();
        if ($status) {
            // Back to index page
            return redirect()->route('stock.index')->with('success', $symbol.' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $symbol.' cannot be deleted, please try again.');
        }
    }
}
