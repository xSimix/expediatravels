(function () {
    'use strict';

    const formatRange = (from, to, total) => {
        return `Mostrando ${from} – ${to} de ${total} integrantes`;
    };

    const initTablePagination = (table) => {
        const pageSize = parseInt(table.dataset.pageSize || '0', 10) || 0;
        if (!pageSize) {
            return;
        }

        const tbody = table.tBodies && table.tBodies[0];
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.rows || []);
        if (rows.length <= pageSize) {
            return;
        }

        let currentPage = 1;
        const totalPages = Math.ceil(rows.length / pageSize);

        const wrapper = table.closest('.admin-table-wrapper');
        const container = document.createElement('div');
        container.className = 'admin-table-pagination';
        container.setAttribute('role', 'navigation');
        container.setAttribute('aria-label', 'Paginación de integrantes');

        const status = document.createElement('div');
        status.className = 'admin-table-pagination__status';
        container.appendChild(status);

        const list = document.createElement('ul');
        list.className = 'admin-table-pagination__list';
        container.appendChild(list);

        const createButton = (label, onClick) => {
            const item = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'admin-table-pagination__button';
            button.textContent = label;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                onClick();
            });
            item.appendChild(button);
            return { item, button };
        };

        const goToPage = (page) => {
            const target = Math.min(Math.max(page, 1), totalPages);
            if (target === currentPage) {
                return;
            }

            currentPage = target;
            render();
        };

        const renderRows = () => {
            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;
            rows.forEach((row, index) => {
                row.style.display = index >= startIndex && index < endIndex ? '' : 'none';
            });
        };

        const renderStatus = () => {
            const start = (currentPage - 1) * pageSize + 1;
            const end = Math.min(currentPage * pageSize, rows.length);
            status.textContent = formatRange(start, end, rows.length);
        };

        const prev = createButton('Anterior', () => {
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });
        prev.button.setAttribute('aria-label', 'Página anterior');
        prev.button.dataset.role = 'prev';
        list.appendChild(prev.item);

        const pageButtons = [];
        for (let page = 1; page <= totalPages; page += 1) {
            const { item, button } = createButton(String(page), () => {
                goToPage(page);
            });
            button.dataset.page = String(page);
            button.setAttribute('aria-label', `Ir a la página ${page}`);
            pageButtons.push(button);
            list.appendChild(item);
        }

        const next = createButton('Siguiente', () => {
            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });
        next.button.setAttribute('aria-label', 'Página siguiente');
        next.button.dataset.role = 'next';
        list.appendChild(next.item);

        const renderButtons = () => {
            prev.button.disabled = currentPage === 1;
            next.button.disabled = currentPage === totalPages;
            pageButtons.forEach((button) => {
                const page = parseInt(button.dataset.page || '0', 10);
                if (page === currentPage) {
                    button.classList.add('admin-table-pagination__button--active');
                    button.setAttribute('aria-current', 'page');
                    button.disabled = true;
                } else {
                    button.classList.remove('admin-table-pagination__button--active');
                    button.removeAttribute('aria-current');
                    button.disabled = false;
                }
            });
        };

        const render = () => {
            renderRows();
            renderStatus();
            renderButtons();
        };

        render();

        if (wrapper) {
            wrapper.appendChild(container);
        } else {
            table.insertAdjacentElement('afterend', container);
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const tables = document.querySelectorAll('table[data-page-size]');
        tables.forEach((table) => initTablePagination(table));
    });
})();
