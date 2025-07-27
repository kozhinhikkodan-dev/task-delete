<?php

namespace App\DataTables\Support;

class DataTableColumn
{
    public string $key;
    public string $title;
    public ?string $class = null;
    public bool $sortable = false;
    public ?string $render = null;
        public ?string $width = null;  // New property


    public static function make(string $key): static
    {
        $column = new static();
        $column->key = $key;
        $column->title = ucfirst(str_replace('_', ' ', $key)); // default
        return $column;
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function class(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function sortable(bool $condition = true): static
    {
        $this->sortable = $condition;
        return $this;
    }

   public function render(?string $template = null): static
{
    if ($template === null) {
        // Default to render the raw column value
        $template = '${data}';
    }

    $this->render = $template;
    return $this;
}
  public function width(string $width): static
    {
        // Example valid inputs: '100px', '10%', '8em', 'auto'
        $this->width = $width;
        return $this;
    }

    public function bold(): static
    {
        return $this->class('font-bold');
    }

    public function center(): static
    {
        return $this->class('text-center');
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'class' => $this->class,
            'sortable' => $this->sortable,
            'render' => $this->render,
            'width' => $this->width,
        ];
    }
}
