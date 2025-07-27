@props(['currentView' => 'table'])

<div class="view-toggle-container">
    <div class="view-toggle-wrapper">
        <div class="view-toggle-switch">
            <input 
                type="checkbox" 
                id="view-toggle" 
                class="view-toggle-input"
                {{ $currentView === 'calendar' ? 'checked' : '' }}
                onchange="toggleView(this)"
            >
            <label for="view-toggle" class="view-toggle-label">
                <span class="view-toggle-option view-toggle-table">
                    <i class="bx bx-table"></i>
                    <span class="view-toggle-text">Table</span>
                </span>
                <span class="view-toggle-option view-toggle-calendar">
                    <i class="bx bx-calendar"></i>
                    <span class="view-toggle-text">Calendar</span>
                </span>
                <span class="view-toggle-slider"></span>
            </label>
        </div>
    </div>
</div>

<script>
function toggleView(checkbox) {
    const isCalendarView = checkbox.checked;
    const currentPath = window.location.pathname;
    
    // Add loading state
    checkbox.disabled = true;
    document.body.style.cursor = 'wait';
    
    // Determine target URL
    let targetUrl;
    if (isCalendarView) {
        // Switch to calendar view
        targetUrl = '{{ route("tasks.calendar") }}';
    } else {
        // Switch to table view
        targetUrl = '{{ route("tasks.index") }}';
    }
    
    // Smooth transition
    document.body.style.opacity = '0.8';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        window.location.href = targetUrl;
    }, 150);
}

// Initialize toggle state based on current page
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('view-toggle');
    const currentPath = window.location.pathname;
    
    if (toggle) {
        // Set initial state
        const isCalendarPage = currentPath.includes('tasks-calendar');
        toggle.checked = isCalendarPage;
        
        // Add smooth animations
        toggle.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.classList.add('changing');
            setTimeout(() => {
                label.classList.remove('changing');
            }, 300);
        });
    }
});
</script> 