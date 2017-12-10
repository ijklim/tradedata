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
        // First field is the unique field
        $this->uniqueFieldName = array_keys($this->validationRules)[0];

    }

    /**
     * Clean up Form input values if necessary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Request
     */
    private function cleanRequest(Request $request) {
        $uniqueFieldName = $this->uniqueFieldName;
        $request->merge([
            $uniqueFieldName => strtoupper($request->$uniqueFieldName)
        ]);
        return $request;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validationRules = $this->validationRules;
        $uniqueFieldName = $this->uniqueFieldName;

        // Clean up input data
        $request = $this->cleanRequest($request);
        // Add 'unique' rule
        $uniqueRule = [
            Rule::unique($this->className::getTableName(), $uniqueFieldName)
        ];
        $validationRules[$uniqueFieldName] = array_merge($validationRules[$uniqueFieldName], $uniqueRule);
        // Validate inputs
        $validatedFields = $this->validate($request, $validationRules);

        try {
            $this->className::create($validatedFields);
            return redirect()->route($this->folderName . '.index')->with('success', $request->$uniqueFieldName.' added successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->$uniqueFieldName);
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
        $validationRules = $this->validationRules;
        $uniqueFieldName = $this->uniqueFieldName;

        // Clean up input data
        $request = $this->cleanRequest($request);
        // Add 'unique' rule
        $uniqueRule = [
            Rule::unique($this->className::getTableName(), $uniqueFieldName)
                ->ignore($stock->$uniqueFieldName, $uniqueFieldName)
        ];
        $validationRules[$uniqueFieldName] = array_merge($validationRules[$uniqueFieldName], $uniqueRule);
        // Validate inputs
        $validatedFields = $this->validate($request, $validationRules);

        try {
            $stock->update($validatedFields);
            // Show update info
            return redirect()->route($this->folderName . '.index')->with('success', $request->$uniqueFieldName . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->$uniqueFieldName);
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
