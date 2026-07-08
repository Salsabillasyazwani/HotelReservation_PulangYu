document.addEventListener("DOMContentLoaded", function () {

    /*element*/
    const modal = document.getElementById("addRoomModal");
    const openBtn = document.getElementById("openAddRoom");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelModal");

    const form = document.getElementById("roomForm");
    const formMethod = document.getElementById("formMethod");
    const roomId = document.getElementById("roomId");

    const modalTitle = document.getElementById("roomModalTitle");
    const submitBtn = document.getElementById("roomSubmitBtn");

    const imageInput = document.getElementById("imageInput");
    const imagePreview = document.getElementById("imagePreview");

    const exportBtn = document.getElementById("exportBtn");
    const exportWrap = document.querySelector(".export-wrap");

    /*form input*/
    const roomNumber = document.getElementById("r_room_number");
    const roomName = document.getElementById("r_room_name");
    const roomType = document.getElementById("r_room_type_id");
    const floor = document.getElementById("r_floor");
    const capacity = document.getElementById("r_capacity");
    const price = document.getElementById("r_price");
    const status = document.getElementById("r_status");
    const description = document.getElementById("r_description");

    /*open modal add*/
    if (openBtn) {
        openBtn.addEventListener("click", function () {
            form.reset();
            form.action = window.roomRoutes.store;
            formMethod.value = "POST";
            roomId.value = "";
            modalTitle.innerText = "Add Room";
            submitBtn.innerText = "Save Room";
            imagePreview.src = "";
            imagePreview.classList.add("hidden");
            modal.classList.remove("hidden");
            document.body.style.overflow = "hidden";
        });
    }

    /*close modal*/
    function closeModal() {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

    modal.addEventListener("click", function (e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeModal();
            if (exportWrap) exportWrap.classList.remove("open");
        }
    });

    /*image preview*/
    if (imageInput) {
        imageInput.addEventListener("change", function (e) {
            const file = e.target.files[0];

            if (!file) {
                imagePreview.src = "";
                imagePreview.classList.add("hidden");
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreview.classList.remove("hidden");
            };
            reader.readAsDataURL(file);
        });
    }

    /*export dropdown*/
    const exportMenu = document.getElementById("exportMenu");

    function positionExportMenu() {
        if (!exportBtn || !exportMenu) return;

        const rect = exportBtn.getBoundingClientRect();
        exportMenu.style.top = (rect.bottom + 8) + "px";
        exportMenu.style.left = "auto";
        exportMenu.style.right = (window.innerWidth - rect.right) + "px";
    }

    if (exportBtn && exportWrap) {
        exportBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const willOpen = !exportWrap.classList.contains("open");
            if (willOpen) positionExportMenu();

            exportWrap.classList.toggle("open");
        });
    }

    document.addEventListener("click", function (e) {
        if (exportWrap && !exportWrap.contains(e.target)) {
            exportWrap.classList.remove("open");
        }
    });

    window.addEventListener("scroll", function () {
        if (exportWrap) exportWrap.classList.remove("open");
    }, true);

    window.addEventListener("resize", function () {
        if (exportWrap) exportWrap.classList.remove("open");
    });

    if (window.roomFormErrors) {
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    /*button loading*/
    form.addEventListener("submit", function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner"></span> Saving...`;
    });

    /*edit room*/
    const editButtons = document.querySelectorAll(".btn-edit");

    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            const room = JSON.parse(this.dataset.room);

            modal.classList.remove("hidden");
            document.body.style.overflow = "hidden";
            modalTitle.innerText = "Edit Room";
            submitBtn.innerText = "Update Room";
            form.action = window.roomRoutes.updateBase + "/" + room.id;
            formMethod.value = "PUT";
            roomId.value = room.id;

            roomNumber.value = room.room_number ?? "";
            roomName.value = room.room_name ?? "";
            roomType.value = room.room_type_id ?? "";
            floor.value = room.floor ?? "";
            capacity.value = room.capacity ?? "";
            price.value = room.price ?? "";
            status.value = room.status ?? "";
            description.value = room.description ?? "";

            if (room.image) {
                imagePreview.src = "/storage/" + room.image;
                imagePreview.classList.remove("hidden");
            } else {
                imagePreview.src = "";
                imagePreview.classList.add("hidden");
            }
        });
    });

    /*view room*/
    const viewButtons = document.querySelectorAll(".btn-view");

    viewButtons.forEach(button => {
        button.addEventListener("click", function () {
            const room = JSON.parse(this.dataset.room);

            Swal.fire({
                title: room.room_number,
                html: `
                    <div style="text-align:left">
                        ${room.image
                            ? `<img src="/storage/${room.image}" style="width:100%; border-radius:12px; margin-bottom:15px;">`
                            : ""
                        }
                        <p><b>Room Name :</b> ${room.room_name ?? "-"}</p>
                        <p><b>Room Type :</b> ${room.room_type?.name ?? "-"}</p>
                        <p><b>Floor :</b> ${room.floor}</p>
                        <p><b>Capacity :</b> ${room.capacity} Guest</p>
                        <p><b>Price :</b> Rp ${Number(room.price).toLocaleString("id-ID")}</p>
                        <p><b>Status :</b> ${room.status}</p>
                        <p><b>Description :</b><br>${room.description ?? "-"}</p>
                    </div>
                `,
                width: 700,
                confirmButtonText: "Close"
            });
        });
    });

    /*delete room*/
    const deleteForms = document.querySelectorAll(".form-delete-room");

    deleteForms.forEach(formDelete => {
        formDelete.addEventListener("submit", function (e) {
            e.preventDefault();

            const roomNumber = this.dataset.roomNumber;

            Swal.fire({
                title: "Delete Room?",
                html: `Room <b>${roomNumber}</b><br><br>cannot be restored after deletion.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#0F1E4D",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) this.submit();
            });
        });
    });

    const flash = document.getElementById("flashSuccess");

    if (flash) {
        const message = flash.dataset.message;

        if (message) {
            Swal.fire({
                icon: "success",
                title: "Success",
                text: message,
                timer: 1800,
                showConfirmButton: false
            });
        }
    }

    /*room status chart*/
    const chartCanvas = document.getElementById("roomChart");

    if (chartCanvas && typeof Chart !== "undefined") {
        const labels = window.roomChartData?.labels ?? [];
        const values = window.roomChartData?.values ?? [];

        new Chart(chartCanvas, {
            type: "doughnut",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ["#22c55e", "#f59e0b", "#ef4444"],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    }
                }
            }
        });
    }

    /*empty state*/
    const roomsTableBody = document.querySelector(".rooms-table tbody");
    const emptyState = document.getElementById("emptyState");

    if (roomsTableBody && emptyState) {
        if (roomsTableBody.children.length === 0) {
            emptyState.classList.remove("hidden");
        } else {
            emptyState.classList.add("hidden");
        }
    }

    /*price format*/
    if (price) {
        price.addEventListener("input", function () {
            let number = this.value.replace(/[^\d]/g, "");
            this.value = number === "" ? "" : number;
        });
    }

    /*row animation*/
    const rows = document.querySelectorAll(".rooms-table tbody tr");

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add("show");
        });
    }, { threshold: 0.15 });

    rows.forEach(row => observer.observe(row));

    /*button ripple*/
    document.querySelectorAll("button").forEach(btn => {
        btn.addEventListener("click", function (e) {
            const circle = document.createElement("span");
            circle.className = "ripple";

            const rect = this.getBoundingClientRect();
            circle.style.left = (e.clientX - rect.left) + "px";
            circle.style.top = (e.clientY - rect.top) + "px";

            this.appendChild(circle);
            setTimeout(() => circle.remove(), 600);
        });
    });

    console.log("Room Management Loaded Successfully");

});
