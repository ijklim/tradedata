<?php

namespace App\Http\Controllers;

use App\DataSource;
use App\Stock;
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
     * Get rules for adding a new record or updating a record.
     *
     * @param  DataSource  $item
     * @return string
     */
    private function getRules(DataSource $item = null)
    {
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
            'api_url' => 'required|min:10'
        ];

        return $rules;
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
        $request->merge($this->getFormattedInputs($request));
        $validatedFields = $this->validate($request, $this->getRules($dataSource));
        
        try {
            $dataSource->update($validatedFields);
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

    /**
     * .
     *
     * @param  \App\DataSource  $dataSource
     * @param  \App\Stock  $stock
     * @return string
     */
    private function apiUrl(DataSource $dataSource, Stock $stock, $startDate, $endDate)
    {
        $maxNoOfRecords = 3;
        switch ($dataSource->domain_name) {
            case 'barchart.com':
                $url = 'https://marketdata.websol.barchart.com/getHistory.json' .
                           '?apikey=' . env('BARCHART_API_KEY', '') .
                           '&symbol=' . $stock->symbol .
                           '&type=daily' .
                           '&startDate=' . $startDate .
                           '&endDate=' . $endDate .
                           '&maxRecords=' . $maxNoOfRecords;
                break;
            default:
                $url = '?';
                break;
        }
        return $url; 
    }

    private function processData(DataSource $dataSource, $jsonResponse)
    {
        switch ($dataSource->domain_name) {
            case 'barchart.com':
                if ($jsonResponse['status']['code'] == 200) {
                    $stockPriceController = new \App\Http\Controllers\StockPriceController(
                        new \App\StockPrice()
                    );
                    $noOfNewRecords = 0;
                    foreach ($jsonResponse['results'] as $result) {
                        $data = [
                            'symbol' => $result['symbol'],
                            'date' => $result['tradingDay'],
                            'open' => $result['open'],
                            'high' => $result['high'],
                            'low' => $result['low'],
                            'close' => $result['close'],
                            'volume' => $result['volume'],
                        ];
                        if ($stockPriceController->insert($data)) {
                            $noOfNewRecords++;
                        }
                    }
                    return 'Data records added: ' . $noOfNewRecords . '/' . count($jsonResponse['results']);
                } else {
                    return 'Error: ' . $jsonResponse['status']['message'];
                }
                break;
            default:
                break;
        }
    }

    public function collectData(DataSource $dataSource, Stock $stock, $startDate, $endDate)
    {
        // Send request to remote api and retrieve json data
        $options = [
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => 0
            ]
        ];
        // TODO: Compute start and end date
        $url = $this->apiUrl($dataSource, $stock, $startDate, $endDate);
        $client = new \GuzzleHttp\Client();
        $request = new \GuzzleHttp\Psr7\Request('GET', $url);
        $promise = $client->sendAsync($request, $options)->then(function ($response) {
            return json_decode($response->getBody(), true);
        });
        return $this->processData($dataSource, $promise->wait());
    }
}
