function renderReparaturRow(entry) {
    const tr = document.createElement('tr');
    tr.setAttribute('data-key', entry.key);

    const levelText = entry.level == 1 ? 'Flugbetrieb nicht möglich' : 'Geringfügig';
    const levelBadge = entry.level == 1 ? '<span class="badge bg-danger">Flugbetrieb nicht möglich</span>' : '<span class="badge bg-warning">Geringfügig</span>';
    const statusBadge = entry.closed == 1 ? '<span class="badge bg-success">gelöst</span>' : '<span class="badge bg-warning text-dark">offen</span>';
    const actionBtn = entry.closed == 1 ? '' : '<button class="btn btn-sm btn-outline-success rep-solve-btn">Als gelöst markieren</button>';

    tr.innerHTML = `
        <td>${entry.key}</td>
        <td>${entry.fluggebiet}</td>
        <td>${entry.text}</td>
        <td>${levelBadge}</td>
        <td>${entry.solvedText ? entry.solvedText : ''}</td>
        <td>${statusBadge}</td>
        <td>${actionBtn}</td>
    `;

    return tr;
}

function loadReparaturen() {
    $.ajax({
        url: 'getReparaturen.php',
        type: 'GET',
        success: function (data) {
            const tbody = document.getElementById('reparaturen-body');
            tbody.innerHTML = '';
            const entries = Array.isArray(data) ? data : JSON.parse(data);
            entries.forEach(e => tbody.appendChild(renderReparaturRow(e)));
        },
        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });
}

function addReparatur() {
    const fluggebiet = document.getElementById('rep-fluggebiet').value;
    const text = document.getElementById('rep-text').value.trim();
    const level = document.getElementById('rep-level').value;
    if (!fluggebiet || !text) {
        if (typeof showToast === 'function') {
            showToast('Hinweis', 'Bitte alle Felder ausfüllen', '', 'error');
        }
        return;
    }
    $.ajax({
        url: 'addReparatur.php',
        type: 'POST',
        data: { fluggebiet: fluggebiet, text: text, level: level },
        success: function () {
            document.getElementById('rep-text').value = '';
            loadReparaturen();
            // Refresh reparaturen badges in flugplanung tab
            if (typeof loadReparaturenCounts === 'function') {
                loadReparaturenCounts();
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });
}

let repSolveModalInstance = null;
let repSolveKey = null;

function openSolveModal(key) {
    repSolveKey = key;
    document.getElementById('rep-solve-key').textContent = key;
    const modalEl = document.getElementById('repSolveModal');
    repSolveModalInstance = new bootstrap.Modal(modalEl);
    document.getElementById('rep-solved-text').value = '';
    repSolveModalInstance.show();
}

function confirmSolve() {
    const solvedText = document.getElementById('rep-solved-text').value.trim();
    if (!repSolveKey) return;
    $.ajax({
        url: 'closeReparatur.php',
        type: 'POST',
        data: { key: repSolveKey, solvedText: solvedText },
        success: function () {
            repSolveModalInstance && repSolveModalInstance.hide();
            repSolveKey = null;
            loadReparaturen();
            // Refresh reparaturen badges in flugplanung tab
            if (typeof loadReparaturenCounts === 'function') {
                loadReparaturenCounts();
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Only initialize when tab is present
    if (!document.getElementById('reparaturen-table')) return;

    loadReparaturen();

    document.getElementById('rep-add-btn').addEventListener('click', addReparatur);

    document.getElementById('reparaturen-body').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('rep-solve-btn')) {
            const key = e.target.closest('tr').getAttribute('data-key');
            openSolveModal(key);
        }
    });

    const confirmBtn = document.getElementById('rep-confirm-solve-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', confirmSolve);
    }
});


