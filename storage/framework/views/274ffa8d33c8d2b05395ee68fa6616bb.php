<script>
    document.addEventListener("DOMContentLoaded", function () {
        /**
         * Check all the permissions
         */
        const checkPermissionAll = document.getElementById("checkPermissionAll");
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');

        checkPermissionAll.addEventListener("click", function () {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });

        function checkPermissionByGroup(className, checkThis) {
            const groupInput = checkThis.querySelector('input[type="checkbox"]');
            if (!groupInput) {
                console.error(`Checkbox input not found inside label with ID ${checkThis.id}.`);
                return;
            }

            const isChecked = groupInput.checked;
            const classCheckBoxes = document.querySelectorAll(`.${className} input[type="checkbox"]`);
            if (!classCheckBoxes.length) {
                console.error(`No checkboxes found for class ${className}.`);
                return;
            }

            classCheckBoxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            implementAllChecked();
        }

        function checkSinglePermission(groupClassName, groupID, countTotalPermission) {
            const classCheckBoxes = document.querySelectorAll(`.${groupClassName} input`);
            const groupIDCheckBox = document.getElementById(groupID);

            const checkedCount = Array.from(classCheckBoxes).filter(checkbox => checkbox.checked).length;

            groupIDCheckBox.checked = (checkedCount === countTotalPermission);

            implementAllChecked();
        }

        function implementAllChecked() {
            const countPermissions = <?php echo e(count($all_permissions)); ?>;
            const countPermissionGroups = <?php echo e(count($permission_groups)); ?>;
            const totalCheckboxes = countPermissions + countPermissionGroups;

            const checkedCount = document.querySelectorAll('input[type="checkbox"]:checked').length;

            checkPermissionAll.checked = (checkedCount >= totalCheckboxes);
        }

        // Expose functions to the global scope if needed
        window.checkPermissionByGroup = checkPermissionByGroup;
        window.checkSinglePermission = checkSinglePermission;
    });
</script><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/roles/partials/scripts.blade.php ENDPATH**/ ?>