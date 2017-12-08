<?php
namespace App\Http\Controllers\Traits;

trait Controller
{
    protected $items;
    protected $folderName;
    protected $validationRules;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view($this->folderName . '.index', ['items' => $this->items::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view($this->folderName . '.change', ['mode' => 'create']);
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