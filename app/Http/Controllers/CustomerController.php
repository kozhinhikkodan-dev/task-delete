<?php

namespace App\Http\Controllers;

use App\DataTables\CustomersDataTable;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Traits\DataTable;
use App\Models\User;
use App\Services\CustomerTaskCreationService;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    use DataTable;

    public function __construct()
    {
        $this->authorizeResource(Customer::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CustomersDataTable $dataTable)
    {
        return $this->renderDataTable($request, $dataTable, 'customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('is_admin', '!=', 1)->get();
        $existingAssignments = []; // Empty for new customers
        return view('customers.form', compact('users', 'existingAssignments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $customer = null;
        $taskResult = null;

        DB::transaction(function () use ($request, &$customer, &$taskResult) {
            // Create the customer
            $customer = Customer::create($request->getData());
            // Create tasks if any counts are specified
            $assignmentData = $request->getAssignmentData();
            $hasTaskCounts = ($customer->total_posters + 
                              $customer->total_video_edits + 
                              $customer->total_blog_posts + 
                              $customer->total_anchoring_video) > 0;

            if ($hasTaskCounts>0) {
                $taskService = new CustomerTaskCreationService();
                $taskResult = $taskService->createTasksForCustomer($customer, $assignmentData);
                if (!$taskResult['success']) {
                    throw new \Exception($taskResult['message']);
                }
            }
        });

        $successMessage = 'Customer created successfully.';
        if ($taskResult && $taskResult['success']) {
            $successMessage .= ' ' . $taskResult['message'];
        }

        return redirect()->route('customers.index')->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $users = User::where('is_admin', '!=', 1)->get();
        
        // Get existing task assignments for this customer
        $taskService = new CustomerTaskCreationService();
        $existingAssignments = $taskService->getExistingAssignments($customer);
        
        return view('customers.form', compact('customer', 'users', 'existingAssignments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        $taskResult = null;

        DB::transaction(function () use ($request, $customer, &$taskResult) {
            // Store original values to check what changed
            $originalCounts = [
                'total_posters' => $customer->total_posters,
                'total_video_edits' => $customer->total_video_edits,
                'total_blog_posts' => $customer->total_blog_posts,
                'total_anchoring_video' => $customer->total_anchoring_video,
            ];

            $originalDates = [
                'service_start_date' => $customer->service_start_date,
                'service_renew_date' => $customer->service_renew_date,
            ];

            // Update the customer
            $customer->update($request->getData());

            // Handle comprehensive task updates
            $assignmentData = $request->getAssignmentData();
            $hasTaskCounts = $customer->total_posters > 0 || 
                           $customer->total_video_edits > 0 || 
                           $customer->total_blog_posts > 0 || 
                           $customer->total_anchoring_video > 0;

            if ($hasTaskCounts || $this->hasExistingTasks($customer, $originalCounts)) {
                $customer->refresh(); // Get updated values
                $taskService = new CustomerTaskCreationService();
                $taskResult = $taskService->handleCustomerUpdate(
                    $customer, 
                    $assignmentData, 
                    $originalCounts,
                    $originalDates
                );
                
                if (!$taskResult['success']) {
                    throw new \Exception($taskResult['message']);
                }
            }
        });

        $successMessage = 'Customer updated successfully.';
        if ($taskResult && $taskResult['success'] && !empty($taskResult['message'])) {
            $successMessage .= ' ' . $taskResult['message'];
        }

        return redirect()->route('customers.index')->with('success', $successMessage);
    }

    /**
     * Check if customer has existing tasks
     */
    private function hasExistingTasks(Customer $customer, array $originalCounts): bool
    {
        foreach ($originalCounts as $count) {
            if ($count > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        DB::transaction(function () use ($customer) {
            $customer->delete();
        });

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
