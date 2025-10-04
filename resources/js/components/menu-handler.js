/**
 * Handle menu item clicks with submenu toggle and first child navigation.
 *
 * @param {HTMLElement} buttonElement - The button element that was clicked
 * @param {string} submenuId - The ID of the submenu to toggle
 * @param {string|null} firstChildRoute - The route of the first child item
 * @param {boolean} isCurrentlyExpanded - Whether the submenu is currently expanded
 * @param {boolean} isOnChildPage - Whether we're currently on one of the child pages
 */
window.handleMenuItemClick = function (buttonElement, submenuId, firstChildRoute, isCurrentlyExpanded, isOnChildPage) {
    const submenu = document.getElementById(submenuId);
    const arrowIcon = buttonElement.querySelector('.menu-item-arrow');
    
    if (!submenu || !arrowIcon) return;
    
    // Toggle submenu visibility with smooth animation.
    const isExpanded = submenu.classList.contains('submenu-expanded');
    
    if (!isExpanded) {
        // Expanding submenu.
        submenu.classList.remove('submenu-collapsed');
        submenu.classList.add('submenu-expanded');
        arrowIcon.setAttribute('icon', 'lucide:chevron-up');
        
        // Add rotation animation to arrow.
        arrowIcon.style.transform = 'rotate(180deg)';
        
        // If there's a first child route, we're not currently on a child page, and we're not already on that route, navigate to it.
        if (firstChildRoute && !isOnChildPage && window.location.href !== firstChildRoute) {
            setTimeout(() => {
                window.location.href = firstChildRoute;
            }, 50);
        }
    } else {
        // Collapsing submenu.
        submenu.classList.remove('submenu-expanded');
        submenu.classList.add('submenu-collapsed');
        arrowIcon.setAttribute('icon', 'lucide:chevron-right');
        
        // Remove rotation from arrow.
        arrowIcon.style.transform = 'rotate(0deg)';
    }
};
