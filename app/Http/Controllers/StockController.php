<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $this->className = get_class($items);
        $this->folderName = $this->className::getFolderName();
        $this->validationRules = [
            'symbol' => ['bail', 'required', 'max:5'],
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
        // Clean up input data
        $request->merge(['symbol' => strtoupper($request->symbol)]);
        // Add 'unique' rule
        $validationRules = $this->validationRules;
        $uniqueRule = [
            Rule::unique($this->className::getTableName(), 'symbol')
        ];
        $validationRules['symbol'] = array_merge($validationRules['symbol'], $uniqueRule);
        $validatedFields = $this->validate($request, $validationRules);

        try {
            $this->className::create($validatedFields);
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
        // Clean up input data
        $request->merge(['symbol' => strtoupper($request->symbol)]);
        // Add 'unique' rule
        $validationRules = $this->validationRules;
        $uniqueRule = [
            Rule::unique($this->className::getTableName(), 'symbol')
                ->ignore($stock->symbol, 'symbol')
        ];
        $validationRules['symbol'] = array_merge($validationRules['symbol'], $uniqueRule);
        $validatedFields = $this->validate($request, $validationRules);

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
