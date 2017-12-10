<?php
namespace App\Http\Controllers\Traits;

trait Controller
{
    protected $items;
    protected $className;
    protected $folderName;
    protected $validationRules;
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
     * Return a set of parameters to be passed to a view.
     *
     * @return array
     */
    private function getViewParameters($parameters = []) {
        return array_merge(
            [
                'folderName' => $this->folderName,                                  // e.g. data-source 
                'itemName' => ucwords(str_replace('-', ' ', $this->folderName))     // e.g. Data Source
            ],
            $parameters
        );
    }

    /**
     * Process error and return appropriate message.
     *
     * @param  Exception  $error
     * @param  string  $symbol
     * @return string
     */
    private function processError($error, $key) {
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