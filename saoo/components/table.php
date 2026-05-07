<?php
/**
 * ChronoGen Data Table Component
 * Reusable table with search, filter, sort capabilities
 * 
 * Usage: renderTable($headers, $rows, $actions);
 */

function renderTable($headers, $rows, $table_id = 'data-table', $enable_search = true, $enable_sort = true) {
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- Search & Filter Bar -->
        <?php if ($enable_search): ?>
        <div class="p-6 border-b border-slate-200">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex-1 w-full md:w-auto">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" 
                               id="search-<?= $table_id ?>" 
                               placeholder="Search..." 
                               class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               onkeyup="filterTable('<?= $table_id ?>')">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportTableCSV('<?= $table_id ?>')" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium text-sm">
                        <i class="fas fa-download mr-2"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full" id="<?= $table_id ?>">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <?php foreach ($headers as $header): ?>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hover:bg-slate-100 cursor-pointer transition-colors <?= $enable_sort ? 'sortable' : '' ?>" 
                            onclick="<?= $enable_sort ? "sortTable('" . $table_id . "', this)" : '' ?>">
                            <div class="flex items-center gap-2">
                                <?= htmlspecialchars($header) ?>
                                <?php if ($enable_sort): ?>
                                <i class="fas fa-arrow-down text-slate-300 opacity-0 text-xs"></i>
                                <?php endif; ?>
                            </div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (empty($rows)): 
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td colspan="<?= count($headers) ?>" class="px-6 py-8 text-center text-slate-500">
                                <i class="fas fa-inbox text-3xl mb-3 opacity-50 block"></i>
                                <p class="font-medium">No data found</p>
                            </td>
                        </tr>
                    <?php 
                    else:
                        foreach ($rows as $row): 
                    ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                            <?php foreach ($row as $cell): ?>
                            <td class="px-6 py-3 text-slate-800">
                                <?php 
                                if (is_array($cell)) {
                                    echo '<span class="badge badge-' . ($cell['type'] ?? 'primary') . '">' . htmlspecialchars($cell['text']) . '</span>';
                                } else {
                                    // Allow raw HTML for complex cells (common in this project's architecture)
                                    echo $cell ?? '-';
                                }
                                ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (optional) -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
            <p class="text-sm text-slate-600">Showing <strong><?= count($rows) ?></strong> results</p>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-sm font-medium">← Previous</button>
                <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-sm font-medium">Next →</button>
            </div>
        </div>
    </div>

    <?php
    // Ensure scripts are defined only once
    static $scripts_rendered = false;
    if (!$scripts_rendered) {
        $scripts_rendered = true;
        ?>
        <script>
            // Search functionality
            function filterTable(tableId) {
                const input = document.getElementById('search-' + tableId);
                const filter = input?.value.toUpperCase();
                const table = document.getElementById(tableId);
                const tr = table?.getElementsByTagName('tr');
                
                if (!table) return;
                
                for (let i = 1; i < tr.length; i++) {
                    const text = tr[i].textContent || tr[i].innerText;
                    tr[i].style.display = text.toUpperCase().includes(filter) ? '' : 'none';
                }
            }

            // Sort functionality
            function sortTable(tableId, header) {
                const table = document.getElementById(tableId);
                const tbody = table?.querySelector('tbody');
                if (!table || !tbody) return;
                
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const colIndex = Array.from(header.parentNode.children).indexOf(header);
                const isAsc = !header.classList.toggle('asc');
                
                rows.sort((a, b) => {
                    const aVal = a.cells[colIndex].textContent.trim();
                    const bVal = b.cells[colIndex].textContent.trim();
                    return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
                
                rows.forEach(row => tbody.appendChild(row));
            }

            // Export to CSV
            function exportTableCSV(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;
                
                let csv = [];
                const rows = table.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td, th');
                    const csvRow = [];
                    cols.forEach(col => csvRow.push(col.textContent.trim()));
                    csv.push(csvRow.join(','));
                });
                
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'export.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            }
        </script>
        <?php
    }
}
?>
