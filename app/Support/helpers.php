<?php

use Illuminate\Support\Str;

if (!function_exists('routeActive')) {
    /**
     * Returns a string if the current route matches any given pattern(s).
     *
     * @param array|string $patterns Route name patterns (wildcards allowed)
     * @param string $class Class name to return (default: 'active')
     * @return string
     */
    function routeActive(array|string $patterns, string $class = 'active'): string
    {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return $class;
            }
        }
        return '';
    }


    /**
     * Generates an array of action links for a given model, excluding specified actions.
     *
     * @param mixed $model The model instance for which actions are being rendered.
     * @param array $exclude (Optional) Actions to exclude from the default actions list.
     * @return string A string representing the rendered actions.
     */

    function renderActions($model, $exclude = [])
    {
        $actions = $routes = [];
        $defaultActions = ['view', 'edit', 'delete'];
        $filteredActions = array_diff($defaultActions, $exclude);

        // Infer model and route prefix
        $modelName = is_string($model) ? class_basename($model) : class_basename(get_class($model));
        $routePrefix = Str::plural(Str::kebab($modelName)); // e.g., Role → roles

        // Define route map for each action
        $routeMap = [
            'view' => "$routePrefix.show",
            'edit' => "$routePrefix.edit",
            'delete' => "$routePrefix.destroy",
        ];

        foreach ($filteredActions as $action) {
            $actionName = Str::singular($action);
            $actions[] = [
                'label' => $actionName,
                'route' => $routeMap[$action] ?? null,
            ];
        }

        // dd($actions);

        return view('components.common.datatable.actions', compact('model', 'actions'));
    }
}
