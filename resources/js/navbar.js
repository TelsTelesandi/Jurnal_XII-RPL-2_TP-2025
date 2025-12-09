// TAILWIND MOBILE TOGGLE & SMOOTH NAVIGATION
console.log("🚀 Tailwind mobile toggle loading...");

document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ DOM loaded - initializing...");
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize smooth navigation 
    initSmoothNavigation();
    
    // Initialize scroll spy for active states
    initScrollSpy();
});

function initMobileMenu() {
    console.log("📱 Setting up mobile menu...");
    
    // Get elements with multiple selectors for reliability
    const toggleBtn = document.querySelector("#mobile-menu-toggle") || 
                     document.querySelector("[data-mobile-toggle]") ||
                     document.querySelector(".mobile-menu-toggle");
    
    const mobileMenu = document.querySelector("#mobile-menu") || 
                      document.querySelector("[data-mobile-menu]") ||
                      document.querySelector(".mobile-menu");
    
    const overlay = document.querySelector("#mobile-overlay") || 
                   document.querySelector("[data-mobile-overlay]") ||
                   document.querySelector(".mobile-overlay");
    
    const closeBtn = document.querySelector("#close-mobile-menu");
    const burgerIcon = document.querySelector("#burger-icon");
    const closeIcon = document.querySelector("#close-icon");
    
    // Debug log
    console.log("🔍 Mobile elements:");
    console.log("- Toggle button:", !!toggleBtn, toggleBtn);
    console.log("- Mobile menu:", !!mobileMenu, mobileMenu);
    console.log("- Overlay:", !!overlay, overlay);
    
    if (!toggleBtn || !mobileMenu) {
        console.error("❌ Critical mobile elements missing!");
        return;
    }
    
    let isMenuOpen = false;
    
    function toggleMenu() {
        isMenuOpen = !isMenuOpen;
        console.log("🎯 Toggling menu:", isMenuOpen ? "OPEN" : "CLOSED");
        
        if (isMenuOpen) {
            // Open menu - remove hidden classes, add visible classes
            mobileMenu.classList.remove("translate-x-full", "opacity-0");
            mobileMenu.classList.add("translate-x-0", "opacity-100");
            
            if (overlay) {
                overlay.classList.remove("hidden", "opacity-0");
                overlay.classList.add("opacity-50");
            }
            
            // Toggle icons
            if (burgerIcon && closeIcon) {
                burgerIcon.classList.add("hidden");
                closeIcon.classList.remove("hidden");
            }
            
            // Prevent body scroll
            document.body.classList.add("overflow-hidden");
            toggleBtn.setAttribute("aria-expanded", "true");
            
        } else {
            // Close menu - add hidden classes, remove visible classes  
            mobileMenu.classList.remove("translate-x-0", "opacity-100");
            mobileMenu.classList.add("translate-x-full", "opacity-0");
            
            if (overlay) {
                overlay.classList.remove("opacity-50");
                overlay.classList.add("hidden", "opacity-0");
            }
            
            // Toggle icons
            if (burgerIcon && closeIcon) {
                burgerIcon.classList.remove("hidden");
                closeIcon.classList.add("hidden");
            }
            
            // Allow body scroll
            document.body.classList.remove("overflow-hidden");
            toggleBtn.setAttribute("aria-expanded", "false");
        }
    }
    
    // Event listeners
    toggleBtn.addEventListener("click", function(e) {
        console.log("🖱️ Toggle button clicked!");
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
    });
    
    // Close menu events
    function closeMenu() {
        if (isMenuOpen) {
            console.log("🖱️ Closing menu...");
            isMenuOpen = false;
            mobileMenu.classList.remove("translate-x-0", "opacity-100");
            mobileMenu.classList.add("translate-x-full", "opacity-0");
            
            if (overlay) {
                overlay.classList.remove("opacity-50");
                overlay.classList.add("hidden", "opacity-0");
            }
            
            if (burgerIcon && closeIcon) {
                burgerIcon.classList.remove("hidden");
                closeIcon.classList.add("hidden");
            }
            
            document.body.classList.remove("overflow-hidden");
            toggleBtn.setAttribute("aria-expanded", "false");
        }
    }
    
    // Close button
    if (closeBtn) {
        closeBtn.addEventListener("click", function(e) {
            console.log("🖱️ Close button clicked!");
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
        });
    }
    
    // Overlay click
    if (overlay) {
        overlay.addEventListener("click", function(e) {
            console.log("🖱️ Overlay clicked!");
            closeMenu();
        });
    }
    
    // Close on escape key
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            closeMenu();
        }
    });
    
    // Close when clicking nav links
    const mobileNavLinks = mobileMenu.querySelectorAll("a");
    console.log("🔗 Found", mobileNavLinks.length, "mobile nav links");
    
    mobileNavLinks.forEach(function(link, index) {
        link.addEventListener("click", function() {
            console.log("🖱️ Mobile nav link", index, "clicked");
            closeMenu();
        });
    });
    
    console.log("✅ Mobile menu setup complete!");
}

function initSmoothNavigation() {
    console.log("🎯 Setting up smooth navigation...");
    
    // Check if homepage
    const isHomepage = window.location.pathname === '/' || window.location.pathname === '';
    console.log("📍 Current page:", window.location.pathname, "| Homepage:", isHomepage);
    
    if (isHomepage) {
        // Smooth scroll for anchor links on homepage
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        console.log("🔗 Found", anchorLinks.length, "anchor links");
        
        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href').substring(1);
                const target = document.getElementById(targetId);
                
                if (target) {
                    console.log("🎯 Smooth scrolling to:", targetId);
                    
                    // Calculate offset for fixed navbar (80px)
                    const offsetTop = target.offsetTop - 80;
                    
                    // Smooth scroll with JavaScript (pure implementation)
                    smoothScrollTo(offsetTop, 800); // 800ms duration
                } else {
                    console.log("❌ Target not found:", targetId);
                }
            });
        });
    }
    
    console.log("✅ Smooth navigation setup complete!");
}

// Pure JavaScript smooth scroll implementation
function smoothScrollTo(targetPosition, duration) {
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    let startTime = null;
    
    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
        window.scrollTo(0, run);
        if (timeElapsed < duration) requestAnimationFrame(animation);
    }
    
    // Easing function for smooth animation
    function easeInOutQuad(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return c / 2 * t * t + b;
        t--;
        return -c / 2 * (t * (t - 2) - 1) + b;
    }
    
    requestAnimationFrame(animation);
}

function initScrollSpy() {
    console.log("👁️ Setting up scroll spy...");
    
    // Only run on homepage
    const isHomepage = window.location.pathname === '/' || window.location.pathname === '';
    if (!isHomepage) {
        console.log("❌ Not homepage, skipping scroll spy");
        return;
    }
    
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-link");
    const activeIndicators = document.querySelectorAll(".nav-active-indicator");
    const mobileActiveIndicators = document.querySelectorAll(".mobile-nav-active-indicator");
    
    console.log("📍 Found", sections.length, "sections");
    console.log("🔗 Found", navLinks.length, "nav links");
    console.log("📊 Found", activeIndicators.length, "desktop indicators");
    console.log("📱 Found", mobileActiveIndicators.length, "mobile indicators");
    
    if (sections.length === 0 || navLinks.length === 0) {
        console.log("⚠️ No sections or nav links found");
        return;
    }
    
    let lastActiveSection = "";
    
    function updateActiveNavigation() {
        let currentSection = "";
        
        // Find current section based on scroll position
        sections.forEach(function(section) {
            const sectionTop = section.offsetTop - 100;
            if (window.scrollY >= sectionTop) {
                currentSection = section.getAttribute("id");
            }
        });
        
        // Only update if section changed
        if (currentSection !== lastActiveSection) {
            console.log("📍 Active section changed:", lastActiveSection, "→", currentSection);
            lastActiveSection = currentSection;
            
            // Reset all nav links and indicators
            navLinks.forEach(function(link) {
                // Remove active styles with Tailwind classes
                link.classList.remove("text-blue-600", "font-semibold");
                link.classList.add("text-gray-700", "font-medium");
            });
            
            // Reset all desktop indicators
            activeIndicators.forEach(function(indicator) {
                indicator.classList.remove("scale-x-100");
                indicator.classList.add("scale-x-0");
            });
            
            // Reset all mobile indicators
            mobileActiveIndicators.forEach(function(indicator) {
                indicator.classList.remove("scale-y-100");
                indicator.classList.add("scale-y-0");
            });
            
            // Set active states for current section
            navLinks.forEach(function(link, index) {
                const href = link.getAttribute("href");
                
                if (href && href.includes("#")) {
                    const targetId = href.split("#")[1];
                    
                    if (targetId === currentSection) {
                        // Add active styles with Tailwind classes
                        link.classList.remove("text-gray-700", "font-medium");
                        link.classList.add("text-blue-600", "font-semibold");
                        
                        // Activate desktop indicator
                        if (activeIndicators[index]) {
                            activeIndicators[index].classList.remove("scale-x-0");
                            activeIndicators[index].classList.add("scale-x-100");
                        }
                        
                        // Activate mobile indicator
                        if (mobileActiveIndicators[index]) {
                            mobileActiveIndicators[index].classList.remove("scale-y-0");
                            mobileActiveIndicators[index].classList.add("scale-y-100");
                        }
                    }
                }
            });
        }
    }
    
    // Throttled scroll listener for performance
    let scrollTimeout;
    window.addEventListener("scroll", function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(updateActiveNavigation, 10);
    });
    
    // Set initial active state
    updateActiveNavigation();
    
    console.log("✅ Scroll spy setup complete!");
}

// Add scroll effect to navbar (optional)
function initNavbarScrollEffect() {
    const navbar = document.querySelector("nav");
    if (!navbar) return;
    
    window.addEventListener("scroll", function() {
        if (window.scrollY > 50) {
            navbar.classList.add("bg-white/98", "shadow-xl");
            navbar.classList.remove("bg-white/95");
        } else {
            navbar.classList.remove("bg-white/98", "shadow-xl");
            navbar.classList.add("bg-white/95");
        }
    });
}

// Initialize navbar scroll effect
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(initNavbarScrollEffect, 100);
});

console.log("📊 Tailwind mobile toggle script loaded!");