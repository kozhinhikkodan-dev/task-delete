<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Datatable extends Component
{
    public $id;
    public $columns;
    public $ajaxUrl;
    public $perPage;
    public $title;
    public $tableClass;

    /**
     * Create a new component instance.
     *
     * @param string $id
     * @param array $columns
     * @param string $ajaxUrl
     * @param int $perPage
     * @param string $title
     * @param string $tableClass
     */
    public function __construct(
        string $id = 'dataTable',
        array $columns = [],
        string $ajaxUrl = '',
        int $perPage = 10,
        string $title = 'DataTable',
        string $tableClass = 'w-full border-collapse align-middle hover:bg-gray-50'
    ) {
        $this->id = $id;
        $this->columns = $columns;
        $this->ajaxUrl = $ajaxUrl;
        $this->perPage = $perPage;
        $this->title = $title;
        $this->tableClass = $tableClass;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('common.components.datatable');
    }
}