/**
 * Library Management System - JavaScript Interactions
 * Software Engineering-I Academic Prototype
 */

document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------------------
    // 1. Live Client-Side Table Filter for Book Catalogue
    // ----------------------------------------------------
    const liveSearchInput = document.getElementById('liveSearchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const catalogueTableBody = document.getElementById('catalogueTableBody');

    if (liveSearchInput && catalogueTableBody) {
        const rows = catalogueTableBody.getElementsByTagName('tr');

        function filterBooks() {
            const query = liveSearchInput.value.toLowerCase().trim();
            const selectedCat = categoryFilter ? categoryFilter.value.toLowerCase().trim() : '';

            let visibleCount = 0;

            for (let row of rows) {
                // Ignore empty-state row if present
                if (row.classList.contains('no-results-row')) continue;

                const titleCell = row.querySelector('.book-title');
                const authorCell = row.querySelector('.book-author');
                const categoryCell = row.querySelector('.book-category');

                const titleText = titleCell ? titleCell.textContent.toLowerCase() : '';
                const authorText = authorCell ? authorCell.textContent.toLowerCase() : '';
                const catText = categoryCell ? categoryCell.textContent.toLowerCase() : '';

                const matchesQuery = !query || titleText.includes(query) || authorText.includes(query);
                const matchesCat = !selectedCat || catText.includes(selectedCat);

                if (matchesQuery && matchesCat) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Show or hide "No matching books" message
            let noResultElem = document.getElementById('noResultsMessage');
            if (visibleCount === 0) {
                if (!noResultElem) {
                    noResultElem = document.createElement('tr');
                    noResultElem.id = 'noResultsMessage';
                    noResultElem.className = 'no-results-row';
                    noResultElem.innerHTML = `<td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-3 d-block mb-2 text-secondary"></i>
                        No books found matching "<strong>${escapeHtml(query)}</strong>".
                    </td>`;
                    catalogueTableBody.appendChild(noResultElem);
                } else {
                    noResultElem.style.display = '';
                }
            } else if (noResultElem) {
                noResultElem.style.display = 'none';
            }
        }

        liveSearchInput.addEventListener('keyup', filterBooks);
        if (categoryFilter) {
            categoryFilter.addEventListener('change', filterBooks);
        }
    }

    // ----------------------------------------------------
    // 2. Demo Credential Quick-Filler on Login Page
    // ----------------------------------------------------
    const fillStudentBtn = document.getElementById('fillStudentBtn');
    const fillAdminBtn = document.getElementById('fillAdminBtn');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');

    if (fillStudentBtn && emailField && passwordField) {
        fillStudentBtn.addEventListener('click', function (e) {
            e.preventDefault();
            emailField.value = 'student@library.com';
            passwordField.value = 'student123';
            highlightInputs([emailField, passwordField]);
        });
    }

    const fillOverdueBtn = document.getElementById('fillOverdueBtn');
    if (fillOverdueBtn && emailField && passwordField) {
        fillOverdueBtn.addEventListener('click', function (e) {
            e.preventDefault();
            emailField.value = 'emma.watson@college.edu';
            passwordField.value = 'student123';
            highlightInputs([emailField, passwordField]);
        });
    }

    if (fillAdminBtn && emailField && passwordField) {
        fillAdminBtn.addEventListener('click', function (e) {
            e.preventDefault();
            emailField.value = 'admin@library.com';
            passwordField.value = 'admin123';
            highlightInputs([emailField, passwordField]);
        });
    }

    function highlightInputs(elements) {
        elements.forEach(el => {
            el.classList.add('is-valid');
            setTimeout(() => el.classList.remove('is-valid'), 1500);
        });
    }

    // ----------------------------------------------------
    // 3. Confirm Deletion Prompts
    // ----------------------------------------------------
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const bookTitle = this.getAttribute('data-book-title') || 'this book';
            if (!confirm(`Are you sure you want to delete "${bookTitle}"? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });

    // ----------------------------------------------------
    // 4. Auto-dismiss alerts after 6 seconds
    // ----------------------------------------------------
    const alertList = document.querySelectorAll('.alert-dismissible');
    alertList.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // Helper: basic string escaping for DOM injection
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
