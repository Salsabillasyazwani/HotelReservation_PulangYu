// =====================================
// FACILITIES.JS (Pure vanilla, no Bootstrap dependency)
// =====================================

const facilities = window.facilities || [];
const updateBaseUrl = window.FacilitiesConfig?.updateBaseUrl || "";

// =====================================
// GENERIC MODAL HELPERS
// =====================================

function openModal(id) {
    document.getElementById(id)?.classList.remove("hidden");
}

function closeModal(id) {
    document.getElementById(id)?.classList.add("hidden");
}

// Close modal when clicking outside the panel
document.addEventListener("click", function (e) {
    if (e.target.classList && e.target.classList.contains("modal-overlay")) {
        e.target.classList.add("hidden");
    }
});

// =====================================
// DETAIL VIEW
// =====================================

function openDrawer(id) {
    const facility = facilities.find(f => f.id == id);
    if (!facility) return;

    document.getElementById("drawerTitle").innerText = facility.name;
    document.getElementById("drawerDescription").innerText = facility.description ?? "-";

    const status = document.getElementById("drawerStatus");
    status.innerHTML = facility.status === "Active"
        ? `<span class="pill pill-green"><i class="bi bi-check-circle me-1"></i>Active</span>`
        : `<span class="pill pill-red"><i class="bi bi-x-circle me-1"></i>Inactive</span>`;

    const roomTypes = document.getElementById("drawerRoomTypes");
    roomTypes.innerHTML = "";

    if (facility.room_types && facility.room_types.length > 0) {
        facility.room_types.forEach(type => {
            roomTypes.innerHTML += `
                <div class="px-3 py-2 rounded-lg bg-slate-50 border text-sm">
                    ${type.name}
                </div>
            `;
        });
    } else {
        roomTypes.innerHTML = `<p class="text-slate-400 text-sm">No Room Type</p>`;
    }

    openModal("detailModalFacility");
}

// =====================================
// ADD
// =====================================

document.getElementById("btnAddFacility")?.addEventListener("click", () => {
    document.getElementById("addFacilityForm")?.reset();
    openModal("addModal");
});

// =====================================
// EDIT
// =====================================

function openEditModal(id) {
    const facility = facilities.find(f => f.id == id);
    if (!facility) return;

    document.getElementById("editName").value = facility.name;
    document.getElementById("editDescription").value = facility.description ?? "";

    if (facility.status === "Active") {
        document.getElementById("editStatusActive").checked = true;
    } else {
        document.getElementById("editStatusInactive").checked = true;
    }

    document.getElementById("editFacilityForm").action = updateBaseUrl + "/" + facility.id;

    openModal("editModal");
}

// =====================================
// DELETE
// =====================================

function confirmDelete(id) {
    if (!confirm("Delete this facility?")) return;

    const form = document.createElement("form");
    form.method = "POST";
    form.action = updateBaseUrl + "/" + id;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
}

// =====================================
// FILTER STATUS
// =====================================

function applyStatusFilter(status) {
    document.querySelectorAll(".facility-row").forEach(row => {
        if (!status) {
            row.style.display = "";
            return;
        }
        row.style.display = row.dataset.status === status ? "" : "none";
    });
}

document.getElementById("btnFilter")?.addEventListener("click", function () {
    applyStatusFilter(document.getElementById("filterStatus").value);
});

document.getElementById("btnReset")?.addEventListener("click", function () {
    document.getElementById("filterStatus").value = "";
    applyStatusFilter("");
});

// =====================================
// EXPORT (Excel & PDF) — ikut filter status aktif
// =====================================

function getVisibleFacilitiesData() {
    const visibleRows = Array.from(document.querySelectorAll(".facility-row"))
        .filter(row => row.style.display !== "none");

    return visibleRows.map(row => {
        const id = row.getAttribute("data-facility-id");
        const facility = facilities.find(f => f.id == id);
        if (!facility) return null;
        const roomTypeNames = (facility.room_types || []).map(rt => rt.name).join(", ") || "-";
        return { ...facility, roomTypeNames };
    }).filter(Boolean);
}

function dateStamp() {
    const d = new Date();
    return `${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, "0")}${String(d.getDate()).padStart(2, "0")}`;
}

document.getElementById("btnExportExcel")?.addEventListener("click", function () {
    const rows = getVisibleFacilitiesData();
    if (rows.length === 0) {
        alert("Tidak ada data untuk diexport.");
        return;
    }

    const data = rows.map((f, i) => ({
        No: i + 1,
        "Nama Fasilitas": f.name,
        "Deskripsi": f.description || "-",
        "Digunakan di Tipe Kamar": f.roomTypeNames,
        "Status": f.status,
    }));

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Facilities");
    XLSX.writeFile(wb, `facilities_${dateStamp()}.xlsx`);
});

document.getElementById("btnExportPdf")?.addEventListener("click", function () {
    try {
        const rows = getVisibleFacilitiesData();
        if (rows.length === 0) {
            alert("Tidak ada data untuk diexport.");
            return;
        }

        if (!window.jspdf || !window.jspdf.jsPDF) {
            alert("Library PDF belum termuat. Coba refresh halaman.");
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(14);
        doc.text("Facilities - Hotel Pulang Yo", 14, 15);

        doc.autoTable({
            startY: 22,
            head: [["No", "Nama Fasilitas", "Deskripsi", "Digunakan di Tipe Kamar", "Status"]],
            body: rows.map((f, i) => [i + 1, f.name, f.description || "-", f.roomTypeNames, f.status]),
            styles: { fontSize: 9 },
            headStyles: { fillColor: [15, 23, 42] },
        });

        doc.save(`facilities_${dateStamp()}.pdf`);
    } catch (err) {
        console.error("Export PDF error:", err);
        alert("Gagal export PDF. Silakan cek console untuk detail error.");
    }
});
