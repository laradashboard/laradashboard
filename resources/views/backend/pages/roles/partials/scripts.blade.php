<script>
    document.addEventListener("livewire:navigated", function () {
        // Get the main "Select All" checkbox
        const checkPermissionAll = document.getElementById("checkPermissionAll");

        // Direct click handler for "Select All" checkbox
        checkPermissionAll.addEventListener("click", function () {
            const isChecked = this.checked;
            document
                .querySelectorAll('input[type="checkbox"]')
                .forEach((checkbox) => {
                    checkbox.checked = isChecked;
                });
        });

        // Direct click handler for each group checkbox
        document
            .querySelectorAll('[id$="Management"]')
            .forEach((groupCheckbox) => {
                groupCheckbox.addEventListener("click", function () {
                    const isChecked = this.checked;
                    const groupId = this.id;
                    const groupClass = `group-${groupId}`;

                    // Find all checkboxes within this group container
                    const checkboxContainer = document.querySelector(
                        `[data-group="${groupId}"]`
                    );
                    if (checkboxContainer) {
                        const childCheckboxes = checkboxContainer.querySelectorAll(
                            'input[type="checkbox"]'
                        );
                        childCheckboxes.forEach((checkbox) => {
                            checkbox.checked = isChecked;
                        });
                    }

                    updateSelectAllState();
                });
            });

        // Direct click handler for individual permission checkboxes
        document
            .querySelectorAll('input[name="permissions[]"]')
            .forEach((checkbox) => {
                checkbox.addEventListener("click", function () {
                    // Find the group this checkbox belongs to
                    const groupContainer = this.closest('[data-group]');
                    if (!groupContainer) return;

                    const groupId = groupContainer.getAttribute('data-group');
                    if (!groupId) return;

                    // Get all checkboxes in this group
                    const allCheckboxes = groupContainer.querySelectorAll('input[name="permissions[]"]');
                    const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);

                    // Update the group checkbox state
                    const groupCheckbox = document.getElementById(groupId);
                    if (groupCheckbox) {
                        groupCheckbox.checked = allChecked;
                    }

                    updateSelectAllState();
                });
            });

        // Function to update the "Select All" checkbox state
        function updateSelectAllState() {
            const totalPermissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]').length;
            const checkedPermissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]:checked').length;

            checkPermissionAll.checked = (totalPermissionCheckboxes > 0 &&
                checkedPermissionCheckboxes === totalPermissionCheckboxes);
        }

        // Initialize the correct state for all checkboxes
        updateSelectAllState();
    });
</script>
