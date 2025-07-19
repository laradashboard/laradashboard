<div x-data="{
        notifications: [],
        displayDuration: 5000,
        soundEffect: false,

        addNotification({ variant = 'info', title = null, message = null}) {
            const id = Date.now()
            const notification = { id, variant, title, message }

            // Keep only the most recent 10 notifications
            if (this.notifications.length >= 10) {
                this.notifications.splice(0, this.notifications.length - 9)
            }

            // Add the new notification to the notifications stack
            this.notifications.push(notification)

            if (this.soundEffect) {
                // Play the notification sound (optional)
                try {
                    const notificationSound = new Audio('/sounds/notification.mp3')
                    notificationSound.play().catch(() => {})
                } catch (error) {
                    // Silently ignore audio errors
                }
            }
        },
        removeNotification(id) {
            setTimeout(() => {
                this.notifications = this.notifications.filter(
                    (notification) => notification.id !== id,
                )
            }, 300);
        },
    }"
    x-on:notify.window="addNotification({
        variant: $event.detail.variant,
        title: $event.detail.title,
        message: $event.detail.message,
    })"
    class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex max-w-full flex-col gap-3 md:right-4 md:left-auto md:max-w-sm"
    x-on:mouseenter="$dispatch('pause-auto-dismiss')"
    x-on:mouseleave="$dispatch('resume-auto-dismiss')">

    <template x-for="(notification, index) in notifications" x-bind:key="notification.id">
        <div>
            <!-- Success Notification -->
            <template x-if="notification.variant === 'success'">
                <div x-data="{ isVisible: false, timeout: null }"
                     x-cloak
                     x-show="isVisible"
                     class="pointer-events-auto relative rounded-md border border-success-200 bg-white text-gray-700 dark:border-success-800 dark:bg-gray-800 dark:text-gray-200 shadow-lg"
                     role="alert"
                     x-on:pause-auto-dismiss.window="clearTimeout(timeout)"
                     x-on:resume-auto-dismiss.window="timeout = setTimeout(() => {(isVisible = false), removeNotification(notification.id) }, displayDuration)"
                     x-init="$nextTick(() => { isVisible = true }), (timeout = setTimeout(() => { isVisible = false, removeNotification(notification.id)}, displayDuration))"
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-end="translate-y-0 opacity-100"
                     x-transition:enter-start="translate-y-2 opacity-0"
                     x-transition:leave="transition duration-300 ease-in"
                     x-transition:leave-end="translate-x-full opacity-0"
                     x-transition:leave-start="translate-x-0 opacity-100">
                    <div class="flex w-full items-center gap-3 bg-success-50 dark:bg-success-900/20 rounded-md p-4">
                        <!-- Icon -->
                        <div class="rounded-full bg-success-100 dark:bg-success-800/30 p-1.5 text-success-600 dark:text-success-400" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Title & Message -->
                        <div class="flex flex-col gap-1 flex-1">
                            <h3 x-cloak x-show="notification.title" class="text-sm font-semibold text-success-800 dark:text-success-200" x-text="notification.title"></h3>
                            <p x-cloak x-show="notification.message" class="text-sm text-gray-700 dark:text-gray-300" x-text="notification.message"></p>
                        </div>

                        <!-- Dismiss Button -->
                        <button type="button"
                                class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                aria-label="dismiss notification"
                                x-on:click="(isVisible = false), removeNotification(notification.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Error/Danger Notification -->
            <template x-if="notification.variant === 'error' || notification.variant === 'danger'">
                <div x-data="{ isVisible: false, timeout: null }"
                     x-cloak
                     x-show="isVisible"
                     class="pointer-events-auto relative rounded-md border border-error-200 bg-white text-gray-700 dark:border-error-800 dark:bg-gray-800 dark:text-gray-200 shadow-lg"
                     role="alert"
                     x-on:pause-auto-dismiss.window="clearTimeout(timeout)"
                     x-on:resume-auto-dismiss.window="timeout = setTimeout(() => {(isVisible = false), removeNotification(notification.id) }, displayDuration)"
                     x-init="$nextTick(() => { isVisible = true }), (timeout = setTimeout(() => { isVisible = false, removeNotification(notification.id)}, displayDuration))"
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-end="translate-y-0 opacity-100"
                     x-transition:enter-start="translate-y-2 opacity-0"
                     x-transition:leave="transition duration-300 ease-in"
                     x-transition:leave-end="translate-x-full opacity-0"
                     x-transition:leave-start="translate-x-0 opacity-100">
                    <div class="flex w-full items-center gap-3 bg-error-50 dark:bg-error-900/20 rounded-md p-4">
                        <!-- Icon -->
                        <div class="rounded-full bg-error-100 dark:bg-error-800/30 p-1.5 text-error-600 dark:text-error-400" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Title & Message -->
                        <div class="flex flex-col gap-1 flex-1">
                            <h3 x-cloak x-show="notification.title" class="text-sm font-semibold text-error-800 dark:text-error-200" x-text="notification.title"></h3>
                            <p x-cloak x-show="notification.message" class="text-sm text-gray-700 dark:text-gray-300" x-text="notification.message"></p>
                        </div>

                        <!-- Dismiss Button -->
                        <button type="button"
                                class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                aria-label="dismiss notification"
                                x-on:click="(isVisible = false), removeNotification(notification.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Warning Notification -->
            <template x-if="notification.variant === 'warning'">
                <div x-data="{ isVisible: false, timeout: null }"
                     x-cloak
                     x-show="isVisible"
                     class="pointer-events-auto relative rounded-md border border-warning-200 bg-white text-gray-700 dark:border-warning-800 dark:bg-gray-800 dark:text-gray-200 shadow-lg"
                     role="alert"
                     x-on:pause-auto-dismiss.window="clearTimeout(timeout)"
                     x-on:resume-auto-dismiss.window="timeout = setTimeout(() => {(isVisible = false), removeNotification(notification.id) }, displayDuration)"
                     x-init="$nextTick(() => { isVisible = true }), (timeout = setTimeout(() => { isVisible = false, removeNotification(notification.id)}, displayDuration))"
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-end="translate-y-0 opacity-100"
                     x-transition:enter-start="translate-y-2 opacity-0"
                     x-transition:leave="transition duration-300 ease-in"
                     x-transition:leave-end="translate-x-full opacity-0"
                     x-transition:leave-start="translate-x-0 opacity-100">
                    <div class="flex w-full items-center gap-3 bg-warning-50 dark:bg-warning-900/20 rounded-md p-4">
                        <!-- Icon -->
                        <div class="rounded-full bg-warning-100 dark:bg-warning-800/30 p-1.5 text-warning-600 dark:text-warning-400" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Title & Message -->
                        <div class="flex flex-col gap-1 flex-1">
                            <h3 x-cloak x-show="notification.title" class="text-sm font-semibold text-warning-800 dark:text-warning-200" x-text="notification.title"></h3>
                            <p x-cloak x-show="notification.message" class="text-sm text-gray-700 dark:text-gray-300" x-text="notification.message"></p>
                        </div>

                        <!-- Dismiss Button -->
                        <button type="button"
                                class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                aria-label="dismiss notification"
                                x-on:click="(isVisible = false), removeNotification(notification.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Info Notification -->
            <template x-if="notification.variant === 'info'">
                <div x-data="{ isVisible: false, timeout: null }"
                     x-cloak
                     x-show="isVisible"
                     class="pointer-events-auto relative rounded-md border border-blue-light-200 bg-white text-gray-700 dark:border-blue-light-800 dark:bg-gray-800 dark:text-gray-200 shadow-lg"
                     role="alert"
                     x-on:pause-auto-dismiss.window="clearTimeout(timeout)"
                     x-on:resume-auto-dismiss.window="timeout = setTimeout(() => {(isVisible = false), removeNotification(notification.id) }, displayDuration)"
                     x-init="$nextTick(() => { isVisible = true }), (timeout = setTimeout(() => { isVisible = false, removeNotification(notification.id)}, displayDuration))"
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-end="translate-y-0 opacity-100"
                     x-transition:enter-start="translate-y-2 opacity-0"
                     x-transition:leave="transition duration-300 ease-in"
                     x-transition:leave-end="translate-x-full opacity-0"
                     x-transition:leave-start="translate-x-0 opacity-100">
                    <div class="flex w-full items-center gap-3 bg-blue-light-50 dark:bg-blue-light-900/20 rounded-md p-4">
                        <!-- Icon -->
                        <div class="rounded-full bg-blue-light-100 dark:bg-blue-light-800/30 p-1.5 text-blue-light-600 dark:text-blue-light-400" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Title & Message -->
                        <div class="flex flex-col gap-1 flex-1">
                            <h3 x-cloak x-show="notification.title" class="text-sm font-semibold text-blue-light-800 dark:text-blue-light-200" x-text="notification.title"></h3>
                            <p x-cloak x-show="notification.message" class="text-sm text-gray-700 dark:text-gray-300" x-text="notification.message"></p>
                        </div>

                        <!-- Dismiss Button -->
                        <button type="button"
                                class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                aria-label="dismiss notification"
                                x-on:click="(isVisible = false), removeNotification(notification.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
