/**
 * Dynamic Sidebar Generator for PHP React MVC Template Documentation
 * Eliminates code redundancy by dynamically creating navigation and TOC sidebars
 */

class DocumentationSidebar {
    constructor() {
        this.currentPage = this.getCurrentPage();
        this.init();
    }

    getCurrentPage() {
        const path = window.location.pathname;
        const filename = path.split('/').pop();
        return filename || 'index.html';
    }

    init() {
        this.injectCSS();
        this.createLayout();
        this.createNavigationSidebar();
        this.createTOCSidebar();
        this.attachEventListeners();
    }

    injectCSS() {
        const css = `
        /* Sidebar Styles */
        .layout-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #f8f9fa;
            border-right: 1px solid #e1e4e8;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-nav {
            left: 0;
        }
        
        .sidebar-toc {
            right: 0;
            width: 220px;
            border-left: 1px solid #e1e4e8;
            border-right: none;
        }
        
        .main-content {
            margin-left: 250px;
            margin-right: 240px;
            padding: 20px;
            flex: 1;
            min-width: 0; /* Prevents flex item from overflowing */
        }
        
        .sidebar h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #24292e;
            border-bottom: 1px solid #e1e4e8;
            padding-bottom: 0.5rem;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar li {
            margin: 0;
        }
        
        .sidebar a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #586069;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s;
            word-wrap: break-word;
        }
        
        .sidebar a:hover {
            background-color: #e1e4e8;
            color: #24292e;
        }
        
        .sidebar a.active {
            background-color: #0366d6;
            color: white;
        }
        
        .toc-link {
            padding-left: 8px !important;
            border-left: 2px solid transparent;
        }
        
        .toc-link:hover {
            border-left-color: #0366d6;
        }
        
        .toc-link.active {
            border-left-color: #0366d6;
            background-color: transparent;
            color: #0366d6;
            font-weight: 500;
        }
        
        /* Menu Toggle Button */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #0366d6;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.2s;
        }
        
        .menu-toggle:hover {
            background: #0256c9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .menu-toggle:active {
            transform: translateY(0);
        }
        
        /* TOC Toggle Button for Tablets */
        .toc-toggle {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: #28a745;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.2s;
        }
        
        .toc-toggle:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Responsive Design */
        
        /* Large Screens (Desktop) */
        @media (min-width: 1200px) {
            .sidebar {
                width: 280px;
            }
            
            .sidebar-toc {
                width: 250px;
            }
            
            .main-content {
                margin-left: 280px;
                margin-right: 270px;
                padding: 30px;
            }
        }
        
        /* Medium-Large Screens (Laptops) */
        @media (max-width: 1199px) and (min-width: 1025px) {
            .sidebar {
                width: 240px;
            }
            
            .sidebar-toc {
                width: 200px;
            }
            
            .main-content {
                margin-left: 240px;
                margin-right: 220px;
            }
        }
        
        /* Tablets (Portrait and Landscape) */
        @media (max-width: 1024px) and (min-width: 769px) {
            .sidebar-toc {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar-toc.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 20px;
            }
            
            .toc-toggle {
                display: block;
            }
        }
        
        /* Small Tablets and Large Phones */
        @media (max-width: 768px) and (min-width: 481px) {
            .sidebar-nav {
                transform: translateX(-100%);
                width: 280px;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }
            
            .sidebar-nav.open {
                transform: translateX(0);
            }
            
            .sidebar-toc {
                display: none;
            }
            
            .main-content {
                margin-left: 20px;
                margin-right: 20px;
                padding: 20px 15px;
            }
            
            .menu-toggle {
                display: block;
            }
        }
        
        /* Mobile Phones */
        @media (max-width: 480px) {
            .sidebar-nav {
                transform: translateX(-100%);
                width: 100vw;
                padding: 20px 25px;
                box-shadow: none;
            }
            
            .sidebar-nav.open {
                transform: translateX(0);
            }
            
            .sidebar-toc {
                display: none;
            }
            
            .main-content {
                margin-left: 10px;
                margin-right: 10px;
                padding: 15px 10px;
            }
            
            .menu-toggle {
                display: block;
                top: 15px;
                left: 15px;
                padding: 10px;
                font-size: 1.1rem;
            }
            
            /* Improve text readability on mobile */
            .main-content h1 {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .main-content h2 {
                font-size: 1.5rem;
                line-height: 1.3;
            }
            
            .main-content h3 {
                font-size: 1.25rem;
                line-height: 1.4;
            }
            
            .main-content p, .main-content li {
                font-size: 0.95rem;
                line-height: 1.6;
            }
            
            .main-content pre {
                font-size: 0.8rem;
                padding: 12px;
                overflow-x: auto;
            }
            
            .main-content .grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        /* Extra Small Devices */
        @media (max-width: 320px) {
            .main-content {
                margin-left: 5px;
                margin-right: 5px;
                padding: 10px 5px;
            }
            
            .menu-toggle {
                top: 10px;
                left: 10px;
                padding: 8px;
                font-size: 1rem;
            }
            
            .main-content h1 {
                font-size: 1.75rem;
            }
            
            .main-content h2 {
                font-size: 1.35rem;
            }
            
            .main-content h3 {
                font-size: 1.15rem;
            }
            
            .main-content pre {
                font-size: 0.75rem;
                padding: 10px;
            }
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .menu-toggle, .toc-toggle, .sidebar-overlay {
                display: none !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
        
        /* High DPI Displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .sidebar {
                border-right-width: 0.5px;
            }
            
            .sidebar-toc {
                border-left-width: 0.5px;
            }
        }
        
        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .sidebar {
                background: #1f2937;
                border-color: #374151;
            }
            
            .sidebar h3 {
                color: #f9fafb;
                border-color: #374151;
            }
            
            .sidebar a {
                color: #d1d5db;
            }
            
            .sidebar a:hover {
                background-color: #374151;
                color: #f9fafb;
            }
            
            .sidebar a.active {
                background-color: #3b82f6;
            }
            
            .toc-link.active {
                color: #60a5fa;
                border-left-color: #60a5fa;
            }
        }
        
        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            .sidebar, .sidebar-overlay, .menu-toggle, .toc-toggle {
                transition: none !important;
            }
        }
        `;

        const style = document.createElement('style');
        style.textContent = css;
        document.head.appendChild(style);
    }

    createLayout() {
        // Wrap existing body content in layout structure
        const bodyContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
                <span id="menu-icon">☰</span>
            </button>
            <button class="toc-toggle" onclick="toggleTOC()" aria-label="Toggle table of contents">
                📋
            </button>
            <div class="sidebar-overlay" onclick="closeSidebars()"></div>
            <div class="layout-container">
                <nav class="sidebar sidebar-nav" id="nav-sidebar" role="navigation" aria-label="Documentation navigation"></nav>
                <aside class="sidebar sidebar-toc" id="toc-sidebar" role="complementary" aria-label="Table of contents"></aside>
                <main class="main-content" role="main">${bodyContent}</main>
            </div>
        `;
    }

    createNavigationSidebar() {
        const navSidebar = document.getElementById('nav-sidebar');
        
        const navigationPages = [
            { name: '🏠 Home', file: 'index.html', path: '../' },
            { name: '🚀 Getting Started', file: 'getting-started.html', path: '' },
            { name: '⚛️ React SPAs', file: 'spa-development.html', path: '' },
            { name: '🎮 Controllers', file: 'controllers.html', path: '' },
            { name: '🗃️ Models', file: 'models.html', path: '' },
            { name: '🛣️ Routing', file: 'routing.html', path: '' },
            { name: '🗄️ Database', file: 'database.html', path: '' },
            { name: '🔒 Middleware', file: 'middleware.html', path: '' },
            { name: '🚀 Deployment', file: 'deployment.html', path: '' }
        ];

        const externalLinks = [
            { name: '📂 GitHub Repository', url: 'https://github.com/AwaisMehnga/php-react-starter-template' },
            { name: '🐛 Issues', url: 'https://github.com/AwaisMehnga/php-react-starter-template/issues' },
            { name: '📚 Wiki', url: 'https://github.com/AwaisMehnga/php-react-starter-template/wiki' }
        ];

        // Determine if we're in a guide subdirectory
        const isInGuide = window.location.pathname.includes('/guide/');
        
        let navHTML = '<h3>📖 Documentation</h3><ul>';
        
        navigationPages.forEach(page => {
            let href;
            if (page.file === 'index.html') {
                href = isInGuide ? '../index.html' : 'index.html';
            } else {
                href = isInGuide ? page.file : `guide/${page.file}`;
            }
            
            const isActive = this.currentPage === page.file ? 'active' : '';
            navHTML += `<li><a href="${href}" class="${isActive}">${page.name}</a></li>`;
        });
        
        navHTML += '</ul><h3>🔗 Links</h3><ul>';
        
        externalLinks.forEach(link => {
            navHTML += `<li><a href="${link.url}" target="_blank" rel="noopener">${link.name}</a></li>`;
        });
        
        navHTML += '</ul>';
        
        navSidebar.innerHTML = navHTML;
    }

    createTOCSidebar() {
        const tocSidebar = document.getElementById('toc-sidebar');
        const headings = document.querySelectorAll('h1[id], h2[id], h3[id], h4[id]');
        
        if (headings.length === 0) {
            tocSidebar.style.display = 'none';
            document.querySelector('.main-content').style.marginRight = '20px';
            return;
        }

        let tocHTML = '<h3>📋 On This Page</h3><ul>';
        
        headings.forEach(heading => {
            if (heading.id) {
                const text = heading.textContent.replace(/^\d+\.\s*/, '').replace(/^[^\w]*/, '');
                tocHTML += `<li><a href="#${heading.id}" class="toc-link">${text}</a></li>`;
            }
        });
        
        tocHTML += '</ul>';
        tocSidebar.innerHTML = tocHTML;
    }

    attachEventListeners() {
        // Mobile menu toggle
        window.toggleSidebar = () => {
            const sidebar = document.getElementById('nav-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuIcon = document.getElementById('menu-icon');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            
            // Update menu icon
            menuIcon.textContent = sidebar.classList.contains('open') ? '✕' : '☰';
            
            // Prevent body scroll when sidebar is open on mobile
            if (window.innerWidth <= 768) {
                document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
            }
        };

        // TOC toggle for tablets
        window.toggleTOC = () => {
            const tocSidebar = document.getElementById('toc-sidebar');
            tocSidebar.classList.toggle('open');
        };

        // Close sidebars function
        window.closeSidebars = () => {
            const navSidebar = document.getElementById('nav-sidebar');
            const tocSidebar = document.getElementById('toc-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuIcon = document.getElementById('menu-icon');
            
            navSidebar.classList.remove('open');
            tocSidebar.classList.remove('open');
            overlay.classList.remove('active');
            menuIcon.textContent = '☰';
            document.body.style.overflow = '';
        };

        // Table of Contents navigation with smooth scrolling
        document.addEventListener('DOMContentLoaded', () => {
            const tocLinks = document.querySelectorAll('.toc-link');
            const sections = [];
            
            // Build sections array for scroll tracking
            tocLinks.forEach(link => {
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    sections.push({
                        id: targetId,
                        element: targetElement,
                        link: link
                    });
                }
            });
            
            // Smooth scrolling for TOC links
            tocLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = e.target.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        // Close sidebars on mobile after clicking TOC link
                        if (window.innerWidth <= 1024) {
                            window.closeSidebars();
                        }
                        
                        const offsetTop = targetElement.offsetTop - 80;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                        
                        updateActiveTocLink(e.target);
                    }
                });
            });
            
            // Update active TOC link function
            const updateActiveTocLink = (activeLink = null) => {
                tocLinks.forEach(link => link.classList.remove('active'));
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            };
            
            // Scroll spy for automatic TOC highlighting
            const handleScroll = () => {
                const scrollPos = window.scrollY + 100;
                let activeSection = null;
                
                sections.forEach(section => {
                    if (section.element.offsetTop <= scrollPos) {
                        activeSection = section;
                    }
                });
                
                if (activeSection) {
                    updateActiveTocLink(activeSection.link);
                }
            };
            
            // Throttled scroll handler
            let scrollTimeout;
            window.addEventListener('scroll', () => {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                scrollTimeout = setTimeout(handleScroll, 10);
            });
            
            // Initial call to set active link
            handleScroll();
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('nav-sidebar');
            const tocSidebar = document.getElementById('toc-sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            const tocToggle = document.querySelector('.toc-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target) && 
                sidebar.classList.contains('open')) {
                window.closeSidebars();
            }
            
            if (window.innerWidth <= 1024 && window.innerWidth > 768 &&
                !tocSidebar.contains(e.target) && 
                !tocToggle.contains(e.target) && 
                tocSidebar.classList.contains('open')) {
                tocSidebar.classList.remove('open');
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('nav-sidebar');
            const tocSidebar = document.getElementById('toc-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuIcon = document.getElementById('menu-icon');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                menuIcon.textContent = '☰';
                document.body.style.overflow = '';
            }
            
            if (window.innerWidth > 1024) {
                tocSidebar.classList.remove('open');
            }
        });

        // Keyboard navigation support
        document.addEventListener('keydown', (e) => {
            // ESC key closes sidebars
            if (e.key === 'Escape') {
                window.closeSidebars();
            }
            
            // Alt + M toggles navigation sidebar
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                window.toggleSidebar();
            }
            
            // Alt + T toggles TOC sidebar
            if (e.altKey && e.key === 't') {
                e.preventDefault();
                window.toggleTOC();
            }
        });

        // Touch gesture support for mobile
        let touchStartX = 0;
        let touchStartY = 0;
        
        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });
        
        document.addEventListener('touchmove', (e) => {
            if (!touchStartX || !touchStartY) return;
            
            const touchEndX = e.touches[0].clientX;
            const touchEndY = e.touches[0].clientY;
            const diffX = touchStartX - touchEndX;
            const diffY = touchStartY - touchEndY;
            
            // Swipe detection (more horizontal than vertical movement)
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                const sidebar = document.getElementById('nav-sidebar');
                
                // Swipe right to open, swipe left to close
                if (diffX < -50 && touchStartX < 50 && window.innerWidth <= 768) {
                    // Swiped right from edge
                    if (!sidebar.classList.contains('open')) {
                        window.toggleSidebar();
                    }
                } else if (diffX > 50 && sidebar.classList.contains('open')) {
                    // Swiped left
                    window.closeSidebars();
                }
            }
            
            touchStartX = 0;
            touchStartY = 0;
        });
    }

    updateActiveTocLink(activeLink = null) {
        const tocLinks = document.querySelectorAll('.toc-link');
        tocLinks.forEach(link => link.classList.remove('active'));
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
}

// Initialize sidebar when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new DocumentationSidebar();
    });
} else {
    new DocumentationSidebar();
}
