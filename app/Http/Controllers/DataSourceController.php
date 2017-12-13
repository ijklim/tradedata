<?php

namespace App\Http\Controllers;

use App\DataSource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataSourceController extends Controller
{
    use \App\Http\Controllers\Traits\Controller;
    
    /**
     * Create a new controller instance.
     *
     * @param  \App\DataSource  $items
     * @return void
     */
    public function __construct(DataSource $items)
    {
        $this->items = $items;
        $this->className = get_class($items);
        $this->folderName = $this->className::getFolderName();
        // First field is the unique field
        $this->uniqueFieldName = 'domain_name';
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
            $uniqueFieldName => $this->className::formatField($uniqueFieldName, $request->$uniqueFieldName)
        ]);
        return $request;
    }

    /**
     * Get rules for adding a new record or updating a record.
     *
     * @param  DataSource  $item
     * @return string
     */
    private function getRules(DataSource $item = null) {
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
            $uniqueFieldName => ['bail', 'required', $uniqueRule, 'min:5'],
            'api_base_url' => 'required|min:10'
        ];

        return $rules;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $uniqueFieldName = $this->uniqueFieldName;

        // Clean up input data
        $request = $this->cleanRequest($request);
        $validatedFields = $this->validate($request, $this->getRules());

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
     * @param  \App\DataSource  $dataSource
     * @return \Illuminate\Http\Response
     */
    public function show(DataSource $dataSource)
    {
        return $this->index();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DataSource  $dataSource
     * @return \Illuminate\Http\Response
     */
    public function edit(DataSource $dataSource)
    {
        return view(
            $this->folderName . '.change',
            $this->getViewParameters(['mode' => 'edit', 'item' => $dataSource])
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DataSource  $dataSource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DataSource $dataSource)
    {
        $uniqueFieldName = $this->uniqueFieldName;

        // Clean up input data
        $request = $this->cleanRequest($request);
        $validatedFields = $this->validate($request, $this->getRules($dataSource));
        
        try {
            $dataSource->update($validatedFields);
            // Show update info
            return redirect()->route($this->folderName . '.index')->with('success', 'Data Source #' . $dataSource->id . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->$uniqueFieldName);
            // Back to edit page
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DataSource  $dataSource
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataSource $dataSource)
    {
        if ($dataSource->delete()) {
            // Back to index page
            return redirect()->route($this->folderName . '.index')->with('success', $dataSource->domain_name . ' deleted successfully.');
        } else {
            // Back to edit page
            return back()->with('error', $dataSource->domain_name . ' cannot be deleted, please try again.');
        }
    }
}
