<style>
    /* Hide dropdown by default */
    .choices__list--dropdown {
        display: none;
        position: absolute !important;
        z-index: 1000;
        background-color: #fff;
        border: 1px solid #ced4da;
        width: 100%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    /* Show when open */
    .choices.is-open .choices__list--dropdown {
        display: block;
    }
</style>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="d-flex card-header justify-content-between align-items-center d-none">
                <div>
                    <h4 class="card-title">{{ $title }}</h4>
                </div>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        This Month
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="">
                        <a href="#!" class="dropdown-item">Download</a>
                        <a href="#!" class="dropdown-item">Export</a>
                        <a href="#!" class="dropdown-item">Import</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    @include('components.common.datatable.search')
                    @yield('card-tools-add-btn')
                </div>

                @hasSection('filters')
                <div class="accordion mt-2" id="datatable-filter-accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="datatable-filter-accordion-header">
                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#datatable-filter-accordion-content" aria-expanded="false"
                                aria-controls="datatable-filter-accordion-content">
                                Filters
                            </button>
                        </h2>
                        <div id="datatable-filter-accordion-content" class="accordion-collapse collapse"
                            aria-labelledby="datatable-filter-accordion-header"
                            data-bs-parent="#datatable-filter-accordion">
                            <div class="accordion-body">
                                @yield('filters')
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            <div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered" id="{{ $id }}">
                        <thead class="bg-light-subtle" id="{{ $id }}-head"></thead>
                        <tbody id="{{ $id }}-body" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>

            <div class="card-footer border-top">
                <nav aria-label="Page navigation example" class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div id="{{ $id }}-pagination-dropdown" class="me-3">
                            <label for="{{ $id }}-perpage" class="me-2 text-sm text-gray-700">Showing</label>
                            <select id="{{ $id }}-perpage" class="border rounded p-1 text-sm per-page-select"></select>
                            <span class="ms-2 text-sm text-gray-700">entries</span>
                        </div>
                        <div id="{{ $id }}-info" class="text-sm text-gray-600"></div>
                    </div>
                    <ul class="pagination justify-content-end mb-0" id="{{ $id }}-pagination"></ul>
                </nav>
            </div>


        </div>
    </div>
</div>

</div>



<script>
    (function () {

        document.querySelectorAll('.filter').forEach(el => {
            el.addEventListener('change', () => {
                const name = el.name;
                let value = el.value;

                // Optional: handle multi-select arrays
                if (el.multiple) {
                    value = Array.from(el.selectedOptions).map(opt => opt.value);
                }

                // Update state
                state.filters[name] = value;

                // Reset to first page on filter change
                state.page = 1;

                // Fetch new data
                fetchData();
            });
        });

        const table = document.getElementById('{{ $id }}');
        const thead = document.getElementById('{{ $id }}-head');
        const tbody = document.getElementById('{{ $id }}-body');
        const searchInput = document.getElementById('{{ $id }}-search');
        const checkAll = document.getElementById('{{ $id }}-check-all');
        const paginationEl = document.getElementById('{{ $id }}-pagination');
        const prevBtn = document.getElementById('{{ $id }}-prev');
        const nextBtn = document.getElementById('{{ $id }}-next');
        const dropdownToggle = table.closest('.card').querySelector('.dropdown-toggle');
        const dropdownMenu = table.closest('.card').querySelector('.dropdown-menu');

        const search = document.querySelector('[name="search"]');

        search.addEventListener('input', () => {
            state.search = search.value;
            state.page = 1;
            fetchData();
        });

        let state = {
            page: 1,
            perPage: {{ $perPage }},
            // perPage: 10,
            search: search.value,
            sortBy: '',
            sortDirection: 'asc',
            filters: {}
        };

        function fetchData() {
            const params = new URLSearchParams();

            // Add core params
            params.append('page', state.page);
            params.append('per_page', state.perPage);
            params.append('search', state.search);
            params.append('sort_by', state.sortBy);
            params.append('sort_direction', state.sortDirection);

            // Add dynamic filters
            for (const [key, value] of Object.entries(state.filters)) {
                if (Array.isArray(value)) {
                    value.forEach(v => {
                        if (v !== null && v !== '') {
                            params.append(`filter[${key}][]`, v);
                        }
                    });
                } else if (value !== null && value !== '') {
                    params.append(`filter[${key}]`, value);
                }
            }

            const queryString = params.toString();

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }
            
            fetch('{{ $ajaxUrl }}?' + params, {
                headers: headers,
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Response is not JSON');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    renderTable(data);
                    updateInfo(data.meta);
                    updatePaginationDropDown(data.meta);
                    updatePagination(data.meta);
                })
                .catch(error => {
                    let columnsCount = thead.querySelectorAll('th').length;
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="' + columnsCount + '" class="p-2 text-center text-gray-600">Error loading data: ' + error.message + '</td></tr>';
                });
        }

        function renderTable(data) {
            let columnsCount = thead.querySelectorAll('th').length;
            tbody.innerHTML = '';
            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="' + columnsCount + '" class="p-2 text-center text-gray-600">No data available</td></tr>';
                return;
            }

            thead.innerHTML = '';

            data.columns.forEach((column, index) => {

                const th = document.createElement('th');

                th.className = 'px-2 py-2 p-2 text-left';
                th.textContent = column.title;

                if (column.width) {
                    th.style.width = column.width;
                }
                thead.appendChild(th);
            })

            data.data.forEach((row, index) => {

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';

                data.columns.forEach((column) => {
                    const td = document.createElement('td');
                    // td.className = 'p-2 border-b border-gray-200';

                    // Basic rendering (can be extended for custom formatting)
                    td.innerHTML = row[column.key] ?? '';

                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
        }

        function updatePaginationDropDown(meta) {
            const select = document.getElementById('{{ $id }}-perpage');
            if (!select) return;

            // Determine current perPage from response meta or fallback default
            let currentPerPage = meta?.per_page || meta?.length || defaultPerPage;
            let perPageOptions = meta?.per_page_options ?? [10, 25, 50, 100];
            // If currentPerPage not in options, add it
            if (!perPageOptions.includes(currentPerPage)) {
                perPageOptions.push(currentPerPage);
                perPageOptions.sort((a, b) => a - b);
            }

            // Clear existing options (optional, if you want dynamic rebuild)
            select.innerHTML = '';
            perPageOptions.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option;
                opt.textContent = option;
                if (option === currentPerPage) opt.selected = true;
                select.appendChild(opt);
            });

            // Listen for changes to update page size and reload data
            select.addEventListener('change', () => {
                state.perPage = parseInt(select.value, 10);
                state.page = 1;  // reset page on per-page change
                fetchData();     // your function to load data via AJAX
            });
        }

        function updateInfo(meta) {
            const total = meta.recordsFiltered ?? meta.recordsTotal;
            const start = (meta.current_page - 1) * meta.per_page + 1;
            let end = start + meta.per_page - 1;
            if (end > total) end = total;

            document.getElementById('{{ $id }}-info').innerHTML =
                `Showing <span class="fw-semibold">${start}</span> to <span class="fw-semibold">${end}</span> of <span class="fw-semibold">${total}</span> entries`;

        }

        function updatePagination(meta) {
            paginationEl.innerHTML = '';

            const currentPage = meta.current_page;
            const perPage = meta.per_page;
            const totalItems = meta.recordsFiltered ?? meta.recordsTotal;
            const totalPages = Math.ceil(totalItems / perPage);

            const prevLi = document.createElement('li');
            prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
            prevLi.innerHTML = `<a class="page-link" href="javascript:void(0);" id="{{ $id }}-prev">Previous</a>`;
            paginationEl.appendChild(prevLi);

            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            if (endPage - startPage + 1 < maxPagesToShow) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = 'page-item' + (i === currentPage ? ' active' : '');
                li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
                li.addEventListener('click', () => {
                    state.page = i;
                    fetchData();
                });
                paginationEl.appendChild(li);
            }

            const nextLi = document.createElement('li');
            nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
            nextLi.innerHTML = `<a class="page-link" href="javascript:void(0);" id="{{ $id }}-next">Next</a>`;
            paginationEl.appendChild(nextLi);

            prevLi.querySelector('a').addEventListener('click', () => {
                if (state.page > 1) {
                    state.page--;
                    fetchData();
                }
            });

            nextLi.querySelector('a').addEventListener('click', () => {
                if (state.page < totalPages) {
                    state.page++;
                    fetchData();
                }
            });
        }


        // Event listeners
        // let searchTimeout;
        // searchInput.addEventListener('input', () => {
        //     clearTimeout(searchTimeout);
        //     searchTimeout = setTimeout(() => {
        //         state.search = searchInput.value;
        //         state.page = 1;
        //         fetchData();
        //     }, 300);
        // });

        // checkAll.addEventListener('change', () => {
        //     const checkboxes = tbody.querySelectorAll('input[type="checkbox"]');
        //     checkboxes.forEach(checkbox => checkbox.checked = checkAll.checked);
        // });

        table.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const column = btn.dataset.column;
                if (state.sortBy === column) {
                    state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortBy = column;
                    state.sortDirection = 'asc';
                }
                fetchData();
            });
        });

        dropdownToggle.addEventListener('click', (e) => {
            e.preventDefault();
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // Initial fetch
        fetchData();
    })();
</script>

<style>
    .table-centered {
        margin-left: auto;
        margin-right: auto;
    }

    .align-middle {
        vertical-align: middle;
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .dropdown-menu {
        transition: opacity 0.2s ease-in-out;
    }

    .page-item.disabled .page-link {
        pointer-events: none;
        opacity: 0.5;
    }

    .avatar-sm {
        width: 2rem;
        height: 2rem;
    }

    .badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1;
    }

    .per-page-select {
        border-radius: 0.5rem !important;
    }
</style>