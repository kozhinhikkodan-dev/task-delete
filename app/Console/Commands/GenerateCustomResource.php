<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;

class GenerateCustomResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:resource {name?} | gen:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate custom resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $name = $this->argument('name');

        if (!$name) {
            $name = text(
                label: 'What is the name of the resource?',
                placeholder: 'E.g., Client',
                hint: 'Enter the resource name',
                validate: fn ($value) => empty(trim($value)) ? 'Resource name cannot be empty.' : null
            );
        }

        // Compute all string transformations once
        $replacements = $this->buildReplacements($name);

        
        // 1. Generate DataTable (from stub)
        $this->generateFromStub(
            'stubs/custom-resource/datatable.stub',
            app_path("DataTables/{$replacements['pluralName']}DataTable.php"),
            $replacements
        );
        $this->info("✅ DataTable created: {$replacements['pluralName']}DataTable");

        // 2. Controller
        $this->generateFromStub(
            'stubs/custom-resource/controller.stub',
            app_path("Http/Controllers/{$replacements['pluralName']}Controller.php"),
            $replacements
        );
        $this->info("✅ Controller created: {$replacements['pluralName']}Controller");

        // 3. Model
        $this->generateFromStub(
            'stubs/custom-resource/model.stub',
            app_path("Models/{$replacements['model']}.php"),
            $replacements
        );
        $this->info("✅ Model created: {$replacements['model']}");

        // 4. Request
        $this->generateFromStub(
            'stubs/custom-resource/request.stub',
            app_path("Http/Requests/{$replacements['model']}Request.php"),
            $replacements
        );
        $this->info("✅ Request created: {$replacements['model']}Request");


        // 5. Policy
        $this->generateFromStub(
            'stubs/custom-resource/policy.stub',
            app_path("Policies/{$replacements['model']}Policy.php"),
            $replacements
        );
        $this->info("✅ Policy created: {$replacements['model']}Policy");

        // 6. View Bundle
        // make base_path("resources/views/{$name}") folder if not exists
        $viewDir = base_path("resources/views/{$replacements['viewPath']}");
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0777, true);
        }

        $this->generateFromStub(
            'stubs/custom-resource/form.stub',
            "{$viewDir}/form.blade.php",
            $replacements
        );
        $this->generateFromStub(
            'stubs/custom-resource/index.stub',
            "{$viewDir}/index.blade.php",
            $replacements
        );
        $this->info("✅ Views created: index and form");


        // 7. Migration
        $this->callSilent('make:migration', [
            'name' => "create_{$replacements['tableName']}_table"
        ]);
        $this->info("✅ Migration created: {$name}Migration");

        // 8. Append route to routes/web/resources.php
        $this->appendRoute($replacements);

    }

    protected function buildReplacements(string $name): array
    {
        // Normalize input to studly case (e.g., supplier_order -> SupplierOrder)
        $model = Str::studly($name);
        $snakeName = Str::snake($model); // e.g., supplier_order

        // Derive display names
        $wordName = Str::title(str_replace('_', ' ', $snakeName)); // e.g., Supplier Order
        $words = explode(' ', $wordName);
        $userPluralName = implode(' ', array_merge(
            array_slice($words, 0, -1),
            [Str::plural(end($words))]
        )); // e.g., Supplier Orders

        return [
            'model' => $model, // e.g., SupplierOrder
            'modelVariable' => Str::camel($model), // e.g., supplierOrder
            'pluralName' => Str::pluralStudly($model), // e.g., SupplierOrders
            'snakeName' => $snakeName, // e.g., supplier_order
            'tableName' => Str::snake(Str::pluralStudly($model)), // e.g., supplier_orders
            'viewPath' => Str::plural($snakeName), // e.g., supplier_orders
            'routeName' => Str::kebab(Str::pluralStudly($model)), // e.g., supplier-orders
            'userPluralName' => $userPluralName, // e.g., Supplier Orders
            'Name' => $wordName, // e.g., Supplier Order
        ];
    }

    protected function generateFromStub(string $stubPath, string $outputPath, array $replacements): void
    {
        $stubFullPath = base_path($stubPath);

        if (!file_exists($stubFullPath)) {
            $this->error("❌ Stub not found: {$stubFullPath}");
            return;
        }

        if (file_exists($outputPath)) {
            $this->error("❌ File already exists: {$outputPath}");
            return;
        }

        $content = str_replace(
            array_map(fn ($key) => "{{ $key }}", array_keys($replacements)),
            array_values($replacements),
            file_get_contents($stubFullPath)
        );

        file_put_contents($outputPath, $content);
    }

    protected function appendRoute(array $replacements): void
    {
        $resourceFile = base_path('routes/resources.php');
        $routeEntry = "    Route::resource('{$replacements['routeName']}', \\App\\Http\\Controllers\\{$replacements['pluralName']}Controller::class);";

        if (!file_exists($resourceFile)) {
            $contents = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
{$routeEntry}
});
PHP;
            file_put_contents($resourceFile, $contents);
            $this->info("✅ Created and added route to: routes/web/resources.php");
            return;
        }

        $existingContent = file_get_contents($resourceFile);
        if (str_contains($existingContent, $routeEntry)) {
            $this->warn("⚠️ Route already exists in resources.php, skipping.");
            return;
        }

        $pattern = '/(Route::group\(\[.*?\],\s*function\s*\(\)\s*\{\n)/s';
        $updatedContent = preg_replace($pattern, "$1{$routeEntry}\n", $existingContent);
        if ($updatedContent !== $existingContent) {
            file_put_contents($resourceFile, $updatedContent);
            $this->info("✅ Appended route to: routes/web/resources.php");
        } else {
            file_put_contents($resourceFile, "\n{$routeEntry}", FILE_APPEND);
            $this->warn("⚠️ Could not locate route group. Route appended at bottom.");
        }
    }
}
