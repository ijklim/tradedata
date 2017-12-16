<?php

namespace App\Http\Controllers;

use App\StockPrice;

class StockPriceController extends Controller
{
    use \App\Http\Controllers\Traits\Controller;
    
    /**
     * Create a new controller instance.
     *
     * @param  StockPrice  $items
     * @return void
     */
    public function __construct(StockPrice $items)
    {
        $this->items = $items;
        $this->className = get_class($items);
        $this->folderName = $this->className::getFolderName();
        // First field is the unique field
        $this->uniqueFieldName = '';

    }
    
    /**
     * Get rules for adding a new record or updating a record.
     *
     * @param  StockPrice  $item
     * @return string
     */
    private function getRules(StockPrice $item = null) {
        $rules = [
            'symbol' => 'required',
            'date' => 'required|date',
            'open' => 'numeric',
            'high' => 'numeric',
            'low' => 'numeric',
            'close' => 'numeric',
            'volume' => 'numeric'
        ];

        return $rules;
    }

    /**
     * Return prices for a particular stock in json format.
     *
     * @param  Stock  $item
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Stock $stock)
    {
        $items = StockPrice::where('symbol', $stock->symbol)->get();
        return view(
            $this->folderName . '.show',
            $this->getViewParameters(compact('items'))
        );
    }

    /**
     * Insert a newly created resource in storage.
     * Different from store() as this method would not return to a user screen.
     *
     * @param  array  $data
     * @return boolean
     */
    public function insert($data)
    {
        try {
            $request = request();
            $request->merge($data);
            $request->merge($this->getFormattedInputs($request));
            $validatedFields = $this->validate($request, $this->getRules());
            $this->className::create($validatedFields);
            return true;
        } catch (\Exception $e) {
            // If insert fail, just return false
            return false;
        }
    }
}
