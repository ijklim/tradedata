<?php
namespace App\Http\Controllers\Traits;

trait Controller
{
    protected $items;
    protected $className;
    protected $folderName;
    protected $uniqueFieldName;
    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view(
            $this->folderName . '.index',
            $this->getViewParameters(['items' => $this->items::all()])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view(
            $this->folderName . '.change',
            $this->getViewParameters(['mode' => 'create'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->merge($this->getFormattedInputs($request));
        $validatedFields = $this->validate($request, $this->getRules());

        try {
            $this->className::create($validatedFields);
            return redirect()
                    ->route($this->folderName . '.index')
                    ->with('success', $request->input($this->uniqueFieldName).' added successfully.');
        } catch (\Exception $e) {
            $errorMessage = $this->processError($e, $request->input($this->uniqueFieldName));
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Return a set of parameters to be passed to a view.
     *
     * @return array
     */
    private function getViewParameters($parameters = [])
    {
        return array_merge(
            [
                'folderName' => $this->folderName,                                  // e.g. data-source 
                'itemName' => ucwords(str_replace('-', ' ', $this->folderName))     // e.g. Data Source
            ],
            $parameters
        );
    }

    /**
     * Retrieve a list of Form input values that require formatting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function getFormattedInputs(\Illuminate\Http\Request $request)
    {
        // Convert request inputs into a collection
        // Apply formatting to each field, formatField is a static function defined in the model
        // Extract the fields that are different after formatting
        return collect($request->input())
                ->map(function ($item, $key) {
                        return [$key => $this->className::formatField($key, $item)];
                    })
                ->collapse()
                ->diffAssoc($request->input())
                ->toArray();
    }

    /**
     * Process error and return appropriate message.
     *
     * @param  Exception  $error
     * @param  string  $symbol
     * @return string
     */
    private function processError($error, $key)
    {
        $errorUniqueViolation = 'UNIQUE constraint failed: ';
        if ($error instanceof \Illuminate\Database\QueryException) {
            switch (substr($error->errorInfo[2], 0, strlen($errorUniqueViolation))) {
                case $errorUniqueViolation:
                    return "$key is already in the system, please enter another value.";
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