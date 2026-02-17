jQuery(function($) {
    // Check for saved theme preference, otherwise use system preference
    const getPreferredTheme = () => {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateToggleButton(theme);
        
        // Remove critical css if it exists (allows switching back to light)
        const criticalStyle = document.getElementById('critical-dark-style');
        if (criticalStyle) {
            criticalStyle.remove();
        }
    };

    // Inject Toggle Button into Body (Fixed Position Strategy)
    // Structure: Wrapper (Pill) -> Sun Icon, Moon Icon, Ball (Slider)
    const toggleBtn = $(`
        <div id="theme-toggle" class="theme-toggle-switch" role="button" aria-label="Toggle Dark Mode">
            <i class="fas fa-sun toggle-icon-light"></i>
            <i class="fas fa-moon toggle-icon-dark"></i>
            <div class="toggle-ball"></div>
        </div>
    `);
    
    // Append to body
    $('body').append(toggleBtn);

    const updateToggleButton = (theme) => {
        // Just toggling the class on the wrapper handles the CSS animation
        /* No manual icon class swapping needed anymore, CSS handles purely by class */
    };

    // Initial Set
    // We check if the inline script worked. If not, we force the theme (might cause flicker, but ensures usage)
    const storedTheme = getPreferredTheme();
    const currentAttr = document.documentElement.getAttribute('data-theme');
    
    if (storedTheme !== currentAttr) {
        setTheme(storedTheme);
    }
    
    // Ensure button state is correct on load
    updateToggleButton(storedTheme);

    // Event Listener
    $('body').on('click', '#theme-toggle', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });
    // Force White Icons in Dark Mode (JS Override for Stubborn Elements)
    function forceDarkIcons() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        // Define target containers for message/contact icons
        const contactContainers = '.sp-contact-email, .sp-contact-mobile, .sp-contact-phone, .sp-contact-social';
        
        if (isDark) {
            // 1. Force Font Icons (Text Color)
            const textIcons = document.querySelectorAll('.fa-envelope, .fa-envelope-o, .icon-envelope, .sp-contact-email i, .sp-contact-email span');
            textIcons.forEach(icon => icon.style.setProperty('color', '#FFFFFF', 'important'));
            
            // 2. Force SVG Icons (Fill & Stroke) - if it is an inline SVG
            const svgElements = document.querySelectorAll(`${contactContainers} svg, ${contactContainers} svg path, ${contactContainers} svg rect, ${contactContainers} svg circle`);
            svgElements.forEach(el => {
                el.style.setProperty('fill', '#FFFFFF', 'important');
                el.style.setProperty('stroke', '#FFFFFF', 'important');
            });

            // 3. Force Image Icons (Filter) - Specific SPPB Class
            const imgIcons = document.querySelectorAll(`${contactContainers} img, #sp-header img.sppb-img-responsive, .sp-contact-email img.sppb-img-responsive`);
            imgIcons.forEach(img => {
                // Ensure we don't invert the logo if it's already handled, or handle it here if it matches
                if (!img.closest('#sp-logo')) { 
                    img.style.filter = 'brightness(0) invert(1)';
                }
            });
            
            // Force Burger Menu (Lines)
            const burgerLines = document.querySelectorAll('.burger-icon > span');
            burgerLines.forEach(line => line.style.setProperty('background-color', '#FFFFFF', 'important'));
             
            // Force Logo (Image) - Explicitly
            const logoImg = document.querySelector('#sp-logo img');
            if (logoImg) logoImg.style.filter = 'brightness(0) invert(1)';
            
        } else {
             // Reset everything
            const allTargets = document.querySelectorAll('.fa-envelope, .fa-envelope-o, .icon-envelope, .sp-contact-email i, .sp-contact-email span');
            allTargets.forEach(el => el.style.removeProperty('color'));
            
            const svgTargets = document.querySelectorAll(`${contactContainers} svg, ${contactContainers} svg path`);
            svgTargets.forEach(el => {
                el.style.removeProperty('fill');
                el.style.removeProperty('stroke');
            });

            const imgIcons = document.querySelectorAll(`${contactContainers} img, #sp-header img.sppb-img-responsive, .sp-contact-email img.sppb-img-responsive`);
            imgIcons.forEach(img => img.style.filter = '');

            const burgerLines = document.querySelectorAll('.burger-icon > span');
            burgerLines.forEach(line => line.style.removeProperty('background-color'));

            const logoImg = document.querySelector('#sp-logo img');
            if (logoImg) logoImg.style.filter = '';
        }
    }

    // Run on toggle and load
    $('body').on('click', '#theme-toggle', function() {
        setTimeout(forceDarkIcons, 50); // Small delay to let theme attribute update
    });
    
    // Observer to watch for theme changes or dynamic content
    const observer = new MutationObserver(forceDarkIcons);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    
    // Initial run
    forceDarkIcons();
});
