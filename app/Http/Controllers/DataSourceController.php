<?php

namespace App\Http\Controllers;

use App\DataSource;
use Illuminate\Http\Request;

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
        $this->folderName = $this->items::first()->getFolderName();
        $this->validationRules = [
            'domain_name' => 'required|min:5',
            'api_base_url' => 'required|min:10'
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
        $this->validate($request, $this->validationRules);
        
        try {
            DataSource::create([
                'domain_name' => $request->domain_name,
                'api_base_url' => $request->api_base_url
            ]);
                return redirect()->route($this->folderName . '.index')->with('success', $request->domain_name.' added successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->domain_name);
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
        $this->validate($request, $this->validationRules);
        
        try {
            $dataSource->update([
                'domain_name' => $request->domain_name,
                'api_base_url' => $request->api_base_url
            ]);
            // Show update info
            return redirect()->route($this->folderName . '.index')->with('success', 'Data Source #' . $dataSource->id . ' updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->domain_name);
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
