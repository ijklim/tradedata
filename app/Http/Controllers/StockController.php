<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    use \App\Http\Controllers\Traits\Controller;

    /**
     * Create a new controller instance.
     *
     * @param  Stock  $items
     * @return void
     */
    public function __construct(Stock $items)
    {
        $this->items = $items;
        $this->folderName = $this->items::first()->getFolderName();
        $this->validationRules = [
            'symbol' => 'required|max:5',
            'name' => 'required|min:5'
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedFields = $this->validate($request, $this->validationRules);
        $validatedFields['symbol'] = strtoupper($validatedFields['symbol']);

        try {
            Stock::create($validatedFields);
            return redirect()->route($this->folderName . '.index')->with('success', $request->symbol.' added successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->symbol);
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Stock  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Stock $stock)
    {
        return view(
            $this->folderName . '.index',
            $this->getViewParameters(['items' => array($stock)])
        );
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
            $this->folderName . '.change', 
            $this->getViewParameters(['mode' => 'edit', 'item' => $stock])
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
        $validatedFields = $this->validate($request, $this->validationRules);
        $validatedFields['symbol'] = strtoupper($validatedFields['symbol']);

        try {
            $stock->update($validatedFields);
            // Show update info
            return redirect()->route($this->folderName . '.index')->with('success', $stock->symbol . ' updated successfully.');
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
            return redirect()->route($this->folderName . '.index')->with('success', $stock->getKeyValue() . ' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $stock->getKeyValue() . ' cannot be deleted, please try again.');
        }
    }
}
