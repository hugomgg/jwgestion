/**
 * Dark Mode Theme Manager
 * Manages theme switching and persistence
 */

(function() {
    'use strict';

    const ThemeManager = {
        THEME_KEY: 'app-theme',
        THEME_LIGHT: 'light',
        THEME_DARK: 'dark',

        /**
         * Initialize theme on page load
         */
        init: function() {
            // Get saved theme or default to light
            const savedTheme = this.getSavedTheme();
            this.setTheme(savedTheme, false);

            // Setup toggle button event
            this.setupToggleButton();
        },

        /**
         * Get saved theme from localStorage
         */
        getSavedTheme: function() {
            const saved = localStorage.getItem(this.THEME_KEY);
            return saved === this.THEME_DARK ? this.THEME_DARK : this.THEME_LIGHT;
        },

        /**
         * Save theme to localStorage
         */
        saveTheme: function(theme) {
            localStorage.setItem(this.THEME_KEY, theme);
        },

        /**
         * Set theme
         */
        setTheme: function(theme, animate = true) {
            const html = document.documentElement;
            const body = document.body;

            if (theme === this.THEME_DARK) {
                html.setAttribute('data-theme', 'dark');
                body.setAttribute('data-theme', 'dark');
            } else {
                html.removeAttribute('data-theme');
                body.removeAttribute('data-theme');
            }

            this.updateToggleIcon(theme);
            this.saveTheme(theme);

            // Dispatch custom event for other scripts to listen
            window.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: theme } 
            }));
        },

        /**
         * Toggle between light and dark theme
         */
        toggleTheme: function() {
            const currentTheme = this.getSavedTheme();
            const newTheme = currentTheme === this.THEME_LIGHT ? this.THEME_DARK : this.THEME_LIGHT;
            this.setTheme(newTheme);
        },

        /**
         * Update toggle button icon
         */
        updateToggleIcon: function(theme) {
            const icon = document.querySelector('.theme-toggle-btn i');
            if (!icon) return;

            if (theme === this.THEME_DARK) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        },

        /**
         * Setup toggle button event listener
         */
        setupToggleButton: function() {
            const self = this;
            
            // Use event delegation for dynamic content
            document.addEventListener('click', function(e) {
                if (e.target.closest('.theme-toggle-btn')) {
                    e.preventDefault();
                    self.toggleTheme();
                }
            });
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            ThemeManager.init();
        });
    } else {
        ThemeManager.init();
    }

    // Make ThemeManager globally available
    window.ThemeManager = ThemeManager;

})();
