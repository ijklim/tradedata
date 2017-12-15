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
        // First field is the unique field
        $this->uniqueFieldName = 'symbol';

    }

    /**
     * Get rules for adding a new record or updating a record.
     *
     * @param  Stock  $item
     * @return string
     */
    private function getRules(Stock $item = null) {
        $uniqueFieldName = $this->uniqueFieldName;
        
        // Construct 'unique' rule
        if (isset($item)) {
            // .update rule
            $uniqueRule = Rule::unique($this->className::getTableName(), $uniqueFieldName)
                            ->ignore($item->$uniqueFieldName, $uniqueFieldName);
        } else {
            // .store rule
            $uniqueRule = Rule::unique($this->className::getTableName(), $uniqueFieldName);
        }

        $rules = [
            $uniqueFieldName => ['bail', 'required', $uniqueRule, 'max:5'],
            'name' => 'required|min:5',
            'data_source_id' => 'nullable|integer|min:0'
        ];

        return $rules;
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
        $request->merge($this->getFormattedInputs($request));
        $validatedFields = $this->validate($request, $this->getRules($stock));

        try {
            $stock->update($validatedFields);
            // Show update info
            return redirect()
                    ->route($this->folderName . '.index')
                    ->with('success', $request->input($this->uniqueFieldName) . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->input($this->uniqueFieldName));
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

    public function collectData(Stock $stock) {
        // Send request to remote api and retrieve json data
        $dataSourceController = new \App\Http\Controllers\DataSourceController($stock->dataSource()->first());
        return $dataSourceController->collectData($stock->dataSource()->first(), $stock);
        // Process data

                    // ->filter(function ($item, $key) {
                    //     return $key == 'results';
                    // });
        return $json['status']['code'];
    }
}
