<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="mt-4 space-y-6"
         x-data="{
            showBackupModal: false,
            backupType: 'core_with_modules',
            includeDatabase: false,
            includeVendor: false,
            isCreatingBackup: false,
            startBackup() {
                this.isCreatingBackup = true;

                fetch('{{ route("admin.core-upgrades.backup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        backup_type: this.backupType,
                        include_database: this.includeDatabase,
                        include_vendor: this.includeVendor
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.isCreatingBackup = false;
                    this.showBackupModal = false;

                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || '{{ __("Failed to create backup") }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.isCreatingBackup = false;
                    alert('{{ __("An error occurred while creating the backup") }}');
                });
            }
         }"
    >
        {{-- Current Version Card --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Current Version') }}</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">v{{ $currentVersion['version'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <iconify-icon icon="lucide:package" class="text-2xl text-indigo-600 dark:text-indigo-400"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('Released:') }} {{ $currentVersion['release_date'] ?? 'N/A' }}
                </p>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Update Status') }}</p>
                        @if($updateInfo && $updateInfo['has_update'])
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ __('Available') }}</p>
                        @else
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ __('Up to Date') }}</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-full {{ $updateInfo && $updateInfo['has_update'] ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-green-100 dark:bg-green-900/30' }} flex items-center justify-center">
                        <iconify-icon icon="{{ $updateInfo && $updateInfo['has_update'] ? 'lucide:arrow-up-circle' : 'lucide:check-circle' }}"
                                      class="text-2xl {{ $updateInfo && $updateInfo['has_update'] ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}"></iconify-icon>
                    </div>
                </div>
                @if($updateInfo && $updateInfo['has_update'])
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        {{ __('Latest: v:version', ['version' => $updateInfo['latest_version']]) }}
                    </p>
                @endif
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Backups') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ count($backups) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <iconify-icon icon="lucide:archive" class="text-2xl text-gray-600 dark:text-gray-400"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('Available backups') }}
                </p>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('PHP Version') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ PHP_VERSION }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <iconify-icon icon="lucide:code" class="text-2xl text-blue-600 dark:text-blue-400"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('Laravel:') }} {{ app()->version() }}
                </p>
            </x-card>
        </div>

        {{-- Update Available Section --}}
        @if($updateInfo && $updateInfo['has_update'])
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:arrow-up-circle" width="20" height="20" class="text-amber-500"></iconify-icon>
                        {{ __('Update Available') }}
                        @if($updateInfo['has_critical'] ?? false)
                            <span class="badge badge-danger">{{ __('Critical') }}</span>
                        @endif
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $updateInfo['latest_update']['title'] ?? 'v'.$updateInfo['latest_version'] }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $updateInfo['latest_update']['description'] ?? '' }}
                            </p>
                        </div>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            v{{ $updateInfo['latest_version'] }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                        @if(isset($updateInfo['latest_update']['release_date']))
                            <span class="flex items-center gap-1">
                                <iconify-icon icon="lucide:calendar" class="text-gray-400"></iconify-icon>
                                {{ \Carbon\Carbon::parse($updateInfo['latest_update']['release_date'])->format('M d, Y') }}
                            </span>
                        @endif
                        @if(isset($updateInfo['latest_update']['formatted_file_size']))
                            <span class="flex items-center gap-1">
                                <iconify-icon icon="lucide:hard-drive" class="text-gray-400"></iconify-icon>
                                {{ $updateInfo['latest_update']['formatted_file_size'] }}
                            </span>
                        @endif
                        @if(isset($updateInfo['latest_update']['min_php_version']))
                            <span class="flex items-center gap-1">
                                <iconify-icon icon="lucide:code" class="text-gray-400"></iconify-icon>
                                PHP {{ $updateInfo['latest_update']['min_php_version'] }}+
                            </span>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button"
                                    id="upgrade-btn"
                                    onclick="startUpgrade('{{ $updateInfo['latest_version'] }}')"
                                    class="btn btn-primary flex items-center justify-center gap-2">
                                <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                                {{ __('Upgrade to v:version', ['version' => $updateInfo['latest_version']]) }}
                            </button>
                            <button type="button"
                                    id="check-updates-btn"
                                    onclick="checkForUpdates()"
                                    class="btn btn-secondary flex items-center justify-center gap-2">
                                <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                                {{ __('Check Again') }}
                            </button>
                        </div>
                    </div>
                </div>
            </x-card>
        @else
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:check-circle" width="20" height="20" class="text-green-500"></iconify-icon>
                        {{ __('System Up to Date') }}
                    </div>
                </x-slot>

                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                        <iconify-icon icon="lucide:check" class="text-3xl text-green-600 dark:text-green-400"></iconify-icon>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('You are running the latest version') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Version :version is the most recent release.', ['version' => $currentVersion['version']]) }}
                    </p>
                    <button type="button"
                            id="check-updates-btn"
                            onclick="checkForUpdates()"
                            class="btn btn-secondary mt-4 inline-flex items-center gap-2">
                        <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                        {{ __('Check for Updates') }}
                    </button>
                </div>
            </x-card>
        @endif

        {{-- Backups Section --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:archive" width="20" height="20" class="text-gray-500"></iconify-icon>
                        {{ __('Backups') }}
                    </div>
                </div>
            </x-slot>

            <x-slot name="headerRight">
                <button type="button"
                        x-on:click="showBackupModal = true"
                        class="btn btn-sm btn-secondary flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                    {{ __('Create Backup') }}
                </button>
            </x-slot>

            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Backup File') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Size') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Created') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($backups as $backup)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $backup['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $backup['created_at'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.core-upgrades.download', ['filename' => $backup['name']]) }}"
                                               class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                               title="{{ __('Download') }}">
                                                <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                                            </a>
                                            <button type="button"
                                                    onclick="restoreBackup('{{ $backup['name'] }}')"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    title="{{ __('Restore') }}">
                                                <iconify-icon icon="lucide:rotate-ccw" class="text-lg"></iconify-icon>
                                            </button>
                                            <form action="{{ route('admin.core-upgrades.delete-backup') }}" method="POST" class="inline"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this backup?') }}')">
                                                @csrf
                                                <input type="hidden" name="backup_file" value="{{ $backup['name'] }}">
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        title="{{ __('Delete') }}">
                                                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <iconify-icon icon="lucide:archive" class="text-2xl text-gray-400"></iconify-icon>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No backups available. Backups are automatically created before upgrades.') }}
                    </p>
                </div>
            @endif
        </x-card>

        {{-- Manual Upload Section --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:upload" width="20" height="20" class="text-gray-500"></iconify-icon>
                    {{ __('Manual Upload') }}
                </div>
            </x-slot>

            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Upload a zip file to manually upgrade the system. This is useful for development or when automatic downloads are not available.') }}
                </p>

                <form id="upload-form" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="upgrade_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Upgrade Package (.zip)') }}
                        </label>
                        <input type="file"
                               id="upgrade_file"
                               name="upgrade_file"
                               accept=".zip"
                               class="block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-medium
                                      file:bg-indigo-50 file:text-indigo-700
                                      dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                                      hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50
                                      cursor-pointer" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Maximum file size: 100MB. Only .zip files are accepted.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox"
                               id="create_backup_upload"
                               name="create_backup"
                               checked
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800">
                        <label for="create_backup_upload" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Create backup before upgrading') }}
                        </label>
                    </div>

                    <div>
                        <button type="button"
                                id="upload-btn"
                                onclick="uploadUpgrade()"
                                class="btn btn-primary flex items-center gap-2">
                            <iconify-icon icon="lucide:upload" class="text-lg"></iconify-icon>
                            {{ __('Upload & Upgrade') }}
                        </button>
                    </div>
                </form>
            </div>
        </x-card>

        {{-- Create Backup Modal --}}
        <x-modals.create-backup />
    </div>

    {{-- Upgrade Modal --}}
    <div id="upgrade-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/20 backdrop-blur-md transition-opacity"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-xl sm:max-w-lg w-full p-6">
                <div class="text-center">
                    <div id="upgrade-icon" class="w-16 h-16 mx-auto rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                        <iconify-icon icon="lucide:loader-2" class="text-3xl text-indigo-600 dark:text-indigo-400 animate-spin"></iconify-icon>
                    </div>
                    <h3 id="upgrade-title" class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('Upgrading...') }}
                    </h3>
                    <p id="upgrade-message" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Please wait while the upgrade is in progress. Do not close this page.') }}
                    </p>
                    <div id="upgrade-progress" class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                            <div id="upgrade-progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="upgrade-status" class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('Initializing...') }}</p>
                    </div>
                </div>
                <div id="upgrade-actions" class="mt-6 hidden">
                    <button type="button" onclick="closeUpgradeModal(); location.reload();" class="btn btn-primary w-full">
                        {{ __('Done') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function checkForUpdates() {
            const btn = document.getElementById('check-updates-btn');
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="text-lg animate-spin"></iconify-icon> {{ __("Checking...") }}';

            fetch('{{ route("admin.core-upgrades.check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '{{ __("Failed to check for updates") }}');
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon> {{ __("Check for Updates") }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon> {{ __("Check for Updates") }}';
            });
        }

        function startUpgrade(version) {
            if (!confirm('{{ __("This will upgrade your system to the latest version. A backup will be created automatically. Continue?") }}')) {
                return;
            }

            document.getElementById('upgrade-modal').classList.remove('hidden');
            updateProgress(10, '{{ __("Creating backup...") }}');

            fetch('{{ route("admin.core-upgrades.upgrade") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    version: version,
                    create_backup: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgress(100, '{{ __("Upgrade completed successfully!") }}');
                    document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:check" class="text-3xl text-green-600 dark:text-green-400"></iconify-icon>';
                    document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4';
                    document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Complete") }}';
                    document.getElementById('upgrade-message').textContent = data.message;
                } else {
                    updateProgress(0, data.message);
                    document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:x" class="text-3xl text-red-600 dark:text-red-400"></iconify-icon>';
                    document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4';
                    document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Failed") }}';
                    document.getElementById('upgrade-message').textContent = data.message;
                }
                document.getElementById('upgrade-progress').classList.add('hidden');
                document.getElementById('upgrade-actions').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                updateProgress(0, '{{ __("An error occurred during upgrade") }}');
                document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:x" class="text-3xl text-red-600 dark:text-red-400"></iconify-icon>';
                document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4';
                document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Failed") }}';
                document.getElementById('upgrade-progress').classList.add('hidden');
                document.getElementById('upgrade-actions').classList.remove('hidden');
            });
        }

        function updateProgress(percent, status) {
            document.getElementById('upgrade-progress-bar').style.width = percent + '%';
            document.getElementById('upgrade-status').textContent = status;
        }

        function closeUpgradeModal() {
            document.getElementById('upgrade-modal').classList.add('hidden');
        }

        function restoreBackup(filename) {
            if (!confirm('{{ __("Are you sure you want to restore from this backup? This will overwrite current files.") }}')) {
                return;
            }

            fetch('{{ route("admin.core-upgrades.restore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    backup_file: filename
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || '{{ __("Failed to restore backup") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            });
        }

        function uploadUpgrade() {
            const fileInput = document.getElementById('upgrade_file');
            const file = fileInput.files[0];

            if (!file) {
                alert('{{ __("Please select a zip file to upload.") }}');
                return;
            }

            if (!file.name.endsWith('.zip')) {
                alert('{{ __("Please select a valid .zip file.") }}');
                return;
            }

            if (!confirm('{{ __("This will upgrade your system using the uploaded file. A backup will be created automatically. Continue?") }}')) {
                return;
            }

            const btn = document.getElementById('upload-btn');
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="text-lg animate-spin"></iconify-icon> {{ __("Uploading...") }}';

            document.getElementById('upgrade-modal').classList.remove('hidden');
            updateProgress(0, '{{ __("Preparing upload...") }}');

            const formData = new FormData();
            formData.append('upgrade_file', file);
            formData.append('create_backup', document.getElementById('create_backup_upload').checked ? '1' : '0');

            // Use XMLHttpRequest for real upload progress tracking
            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 70); // Upload is 70% of total progress
                    const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                    const totalMB = (e.total / (1024 * 1024)).toFixed(2);
                    updateProgress(percentComplete, '{{ __("Uploading:") }} ' + loadedMB + ' MB / ' + totalMB + ' MB (' + percentComplete + '%)');
                }
            });

            // Upload complete, now processing
            xhr.upload.addEventListener('load', function() {
                updateProgress(75, '{{ __("Upload complete. Processing upgrade...") }}');
            });

            // Handle response
            xhr.addEventListener('load', function() {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:upload" class="text-lg"></iconify-icon> {{ __("Upload & Upgrade") }}';

                try {
                    const data = JSON.parse(xhr.responseText);

                    if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                        updateProgress(100, '{{ __("Upgrade completed successfully!") }}');
                        document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:check" class="text-3xl text-green-600 dark:text-green-400"></iconify-icon>';
                        document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4';
                        document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Complete") }}';
                        document.getElementById('upgrade-message').textContent = data.message;
                    } else {
                        updateProgress(0, data.message || '{{ __("Upgrade failed") }}');
                        document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:x" class="text-3xl text-red-600 dark:text-red-400"></iconify-icon>';
                        document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4';
                        document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Failed") }}';
                        document.getElementById('upgrade-message').textContent = data.message || '{{ __("An error occurred during upgrade") }}';
                    }
                } catch (e) {
                    updateProgress(0, '{{ __("Invalid server response") }}');
                    document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:x" class="text-3xl text-red-600 dark:text-red-400"></iconify-icon>';
                    document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4';
                    document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Failed") }}';
                    document.getElementById('upgrade-message').textContent = '{{ __("Could not parse server response") }}';
                }

                document.getElementById('upgrade-progress').classList.add('hidden');
                document.getElementById('upgrade-actions').classList.remove('hidden');
            });

            // Handle errors
            xhr.addEventListener('error', function() {
                console.error('Upload error');
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:upload" class="text-lg"></iconify-icon> {{ __("Upload & Upgrade") }}';

                updateProgress(0, '{{ __("Network error during upload") }}');
                document.getElementById('upgrade-icon').innerHTML = '<iconify-icon icon="lucide:x" class="text-3xl text-red-600 dark:text-red-400"></iconify-icon>';
                document.getElementById('upgrade-icon').className = 'w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4';
                document.getElementById('upgrade-title').textContent = '{{ __("Upgrade Failed") }}';
                document.getElementById('upgrade-message').textContent = '{{ __("A network error occurred. Please check your connection and try again.") }}';
                document.getElementById('upgrade-progress').classList.add('hidden');
                document.getElementById('upgrade-actions').classList.remove('hidden');
            });

            // Handle abort
            xhr.addEventListener('abort', function() {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:upload" class="text-lg"></iconify-icon> {{ __("Upload & Upgrade") }}';
                closeUpgradeModal();
            });

            // Send request
            xhr.open('POST', '{{ route("admin.core-upgrades.upload") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.send(formData);
        }
    </script>
    @endpush
</x-layouts.backend-layout>
