<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $stocks;

    /**
     * Create a new controller instance.
     *
     * @param  Stock  $stocks
     * @return void
     */
    public function __construct(Stock $stocks)
    {
        $this->stocks = $stocks;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('stock.index', ['stocks' => $this->stocks::all()]);
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
        $this->validate($request, [
            'symbol' => 'required|max:5',
            'name' => 'required|min:5'
        ]);

        try {
            // $stock = new Stock;
            // $stock->symbol = strtoupper($request->symbol);
            // $stock->name = $request->name;
            // $status = $stock->save();
            $status = Stock::create([
                'symbol' => strtoupper($request->symbol),
                'name' => $request->name
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $status = false;
            $errorMessage = $request->symbol.' cannot be added. Error: '.$e->errorInfo[2];
        } catch (\Exception $e) {
            $status = false;
            $errorMessage = $request->symbol.' cannot be added. Please try again.';
        }

        if ($status) {
            return redirect()->route('stock.index')->with('success', $request->symbol.' added successfully.');
        } else {
            // Back to add page
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function show(Stock $stock)
    {
        return view('stock.index', ['stocks' => array($stock)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function edit(Stock $stock)
    {
        return view(
            'stock.change', 
            [
                'mode' => 'edit',
                'stock' => $stock
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stock $stock)
    {
        $status = $stock
                    ->update([
                        'symbol' => strtoupper($request->symbol),
                        'name' => $request->name
                    ]);
        if ($status) {
            // Show update info
            return redirect()->route('stock.index')->with('success', $stock->symbol.' updated successfully.');
        } else {
            // Back to edit page
            return back()->withInput()->with('error', $stock->symbol.' cannot be updated, please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stock $stock)
    {
        $status = $stock->delete();
        if ($status) {
            // Back to index page
            return redirect()->route('stock.index')->with('success', $stock->symbol.' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $stock->symbol.' cannot be deleted, please try again.');
        }
    }
}
