<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $stocks;
    protected $validationRules;

    /**
     * Create a new controller instance.
     *
     * @param  Stock  $stocks
     * @return void
     */
    public function __construct(Stock $stocks)
    {
        $this->stocks = $stocks;
        $this->validationRules = [
            'symbol' => 'required|max:5',
            'name' => 'required|min:5'
        ];
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
        $this->validate($request, $this->validationRules);

        try {
            Stock::create([
                'symbol' => strtoupper($request->symbol),
                'name' => $request->name
            ]);
            return redirect()->route('stock.index')->with('success', $request->symbol.' added successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->symbol);
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
        $this->validate($request, $this->validationRules);

        try {
            $stock->update([
                'symbol' => strtoupper($request->symbol),
                'name' => $request->name
            ]);
            // Show update info
            return redirect()->route('stock.index')->with('success', $stock->symbol . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->symbol);
            // Back to edit page
            return back()->withInput()->with('error', $errorMessage);
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
            return redirect()->route('stock.index')->with('success', $stock->symbol . ' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $stock->symbol . ' cannot be deleted, please try again.');
        }
    }


    /**
     * Process error and return appropriate message.
     *
     * @param  Exception  $error
     * @param  string  $symbol
     * @return string
     */
    private function processError($error, $symbol) {
        if ($error instanceof \Illuminate\Database\QueryException) {
            switch ($error->errorInfo[2]) {
                case 'UNIQUE constraint failed: stocks.symbol':
                    return "$symbol is already in the system, please enter another symbol.";
                    break;
                default:
                    return 'Error detected: ' . $error->errorInfo[2];
                    break;
            }
        } else {
            // Should not end up here
            return 'Error detected, please try again.';
        }
    }
}
