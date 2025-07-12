---
layout: default
title: React SPA Development  
nav_order: 9
---

# React SPA Development
{: .no_toc }

Building React Single Page Applications that integrate seamlessly with the PHP backend using modern development patterns and architectural concepts.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## How SPA Integration Works

### Frontend-Backend Architecture

The template implements a **micro-frontend** approach where each React application is a separate module:

```
Backend API (PHP) ←→ React Module 1 (Dashboard)
                 ←→ React Module 2 (Admin Panel)  
                 ←→ React Module 3 (Public Site)
```

### Build System Integration

**Vite** handles the React applications with hot module replacement and optimized builds:

```javascript
// vite.config.js - Multi-entry configuration
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        'afaq': './modules/Afaq/app.jsx',
        'app': './modules/App/app.jsx',
      },
      output: {
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]'
      }
    }
  },
  server: {
    proxy: {
      '/api': 'http://localhost:8000' // Proxy API calls to PHP
    }
  }
});
```

### Component Architecture Patterns

Each React module follows a consistent architectural pattern using modern React concepts:

```jsx
// modules/Dashboard/app.jsx - Entry point
import React from 'react';
import { createRoot } from 'react-dom/client';
import { AppProvider } from './contexts/AppContext';
import Home from './Home.jsx';
import './style.css';

function App() {
    return (
        <AppProvider>
            <ErrorBoundary>
                <Home />
            </ErrorBoundary>
        </AppProvider>
    );
}

// Mount application with error handling
const container = document.getElementById('dashboard-app');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
} else {
    console.error('Dashboard container not found');
}
```

### State Management with Context API

Modern React applications use Context API with useReducer for complex state:

```jsx
// contexts/AppContext.jsx - Centralized state management
import React, { createContext, useContext, useReducer } from 'react';

const AppContext = createContext();

const initialState = {
    user: null,
    loading: false,
    error: null,
    notifications: []
};

function appReducer(state, action) {
    switch (action.type) {
        case 'SET_USER':
            return { ...state, user: action.payload, loading: false };
        case 'SET_LOADING':
            return { ...state, loading: action.payload };
        case 'SET_ERROR':
            return { ...state, error: action.payload, loading: false };
        case 'ADD_NOTIFICATION':
            return {
                ...state,
                notifications: [...state.notifications, action.payload]
            };
        default:
            return state;
    }
}

export function AppProvider({ children }) {
    const [state, dispatch] = useReducer(appReducer, initialState);
    
    return (
        <AppContext.Provider value={{ state, dispatch }}>
            {children}
        </AppContext.Provider>
    );
}

export function useApp() {
    const context = useContext(AppContext);
    if (!context) {
        throw new Error('useApp must be used within AppProvider');
    }
    return context;
}
```

---

## API Integration Patterns

### Custom Hooks for Data Fetching

React applications use custom hooks to interact with the PHP backend API:

```jsx
// hooks/useApi.js - Reusable API hook with error handling
import { useState, useEffect } from 'react';
import { useApp } from '../contexts/AppContext';

export function useApi(url, options = {}) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const { dispatch } = useApp();

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const response = await fetch(`/api${url}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    ...options
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                setData(result);
                setError(null);
            } catch (err) {
                setError(err.message);
                dispatch({ type: 'SET_ERROR', payload: err.message });
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [url, options.method]);

    return { data, loading, error };
}

// hooks/useAuth.js - Authentication state management
export function useAuth() {
    const { state, dispatch } = useApp();
    
    const login = async (credentials) => {
        dispatch({ type: 'SET_LOADING', payload: true });
        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(credentials)
            });
            
            const data = await response.json();
            if (data.success) {
                dispatch({ type: 'SET_USER', payload: data.user });
                localStorage.setItem('token', data.token);
                return true;
            }
            throw new Error(data.message);
        } catch (error) {
            dispatch({ type: 'SET_ERROR', payload: error.message });
            return false;
        }
    };

    const logout = () => {
        dispatch({ type: 'SET_USER', payload: null });
        localStorage.removeItem('token');
    };

    return { user: state.user, login, logout, loading: state.loading };
}
```

### Component Patterns with Hooks

Modern React components use hooks for lifecycle management and side effects:

```jsx
// components/UserDashboard.jsx - Complex component with multiple hooks
import React, { useState, useEffect, useMemo } from 'react';
import { useApi, useAuth } from '../hooks';

function UserDashboard() {
    const { user } = useAuth();
    const [selectedCategory, setSelectedCategory] = useState('all');
    const { data: tools, loading, error } = useApi('/tools', { 
        method: 'GET' 
    });

    // Memoized filtered tools to prevent unnecessary re-renders
    const filteredTools = useMemo(() => {
        if (!tools || selectedCategory === 'all') return tools;
        return tools.filter(tool => tool.category === selectedCategory);
    }, [tools, selectedCategory]);

    // Effect for tracking user activity
    useEffect(() => {
        const trackActivity = () => {
            fetch('/api/user/activity', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page: 'dashboard',
                    timestamp: Date.now()
                })
            });
        };

        const interval = setInterval(trackActivity, 60000); // Track every minute
        return () => clearInterval(interval);
    }, []);

    if (loading) return <LoadingSpinner />;
    if (error) return <ErrorMessage error={error} />;

    return (
        <div className="dashboard">
            <UserHeader user={user} />
            <CategoryFilter 
                selected={selectedCategory}
                onChange={setSelectedCategory}
            />
            <ToolGrid tools={filteredTools} />
        </div>
    );
}
```

---

## Vite Configuration

### Basic Setup

The `vite.config.js` is pre-configured for multiple SPA entries:

```javascript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [react()],
    
    // Multiple entry points for different SPAs
    build: {
        rollupOptions: {
            input: {
                home: resolve(__dirname, 'modules/Home/app.jsx'),
                afaq: resolve(__dirname, 'modules/Afaq/app.jsx'),
                modal: resolve(__dirname, 'modules/Modal/Modal.jsx'),
            },
            output: {
                entryFileNames: '[name]/main.js',
                chunkFileNames: '[name]/chunks/[name].js',
                assetFileNames: '[name]/[name].[ext]'
            }
        },
        outDir: 'build',
    },
    
    // Development server
    server: {
        port: 3000,
        proxy: {
            '/api': 'http://localhost:8000'
        }
    }
});
```

### Environment Variables

Create `.env` files for different environments:

```bash
# .env.development
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=My App Dev

# .env.production  
VITE_API_URL=https://myapp.com/api
VITE_APP_NAME=My App
```

---

## Creating SPAs

### SPA Structure

Each SPA has its own module directory:

```
modules/
├── Home/
│   ├── app.jsx          # Entry point
│   ├── Home.jsx         # Main component
│   ├── App.css          # Styles
│   └── components/      # Shared components
│       ├── Header.jsx
│       └── Footer.jsx
└── Dashboard/
    ├── app.jsx
    ├── Dashboard.jsx
    └── components/
        ├── Sidebar.jsx
        └── Widget.jsx
```

### Basic SPA Entry Point

```jsx
// modules/Home/app.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import Home from './Home.jsx';
import './App.css';

// Create root and render
const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<Home />);
```

### Main Component

```jsx
// modules/Home/Home.jsx
import React, { useState, useEffect } from 'react';
import Header from './components/Header.jsx';
import Footer from './components/Footer.jsx';

function Home() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const response = await fetch('/api/v1/data');
            const result = await response.json();
            setData(result.data);
        } catch (error) {
            console.error('Error fetching data:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <div className="loading">Loading...</div>;
    }

    return (
        <div className="app">
            <Header />
            <main className="main-content">
                <h1>Welcome to Home SPA</h1>
                <div className="data-grid">
                    {data.map(item => (
                        <div key={item.id} className="data-card">
                            <h3>{item.title}</h3>
                            <p>{item.description}</p>
                        </div>
                    ))}
                </div>
            </main>
            <Footer />
        </div>
    );
}

export default Home;
```

## Advanced Component Patterns

### Higher-Order Components (HOCs) for Reusability

HOCs allow component logic reuse across the application:

```jsx
// hocs/withAuth.jsx - Authentication wrapper
import React, { useEffect } from 'react';
import { useAuth } from '../hooks/useAuth';

function withAuth(WrappedComponent) {
    return function AuthenticatedComponent(props) {
        const { user, loading } = useAuth();
        
        useEffect(() => {
            if (!loading && !user) {
                window.location.href = '/login';
            }
        }, [user, loading]);

        if (loading) {
            return <div className="auth-loading">Checking authentication...</div>;
        }

        if (!user) {
            return null; // Redirecting...
        }

        return <WrappedComponent {...props} user={user} />;
    };
}

// Usage
const ProtectedDashboard = withAuth(Dashboard);
```

### Compound Components Pattern

Components that work together to form a cohesive interface:

```jsx
// components/Modal/Modal.jsx - Compound component system
import React, { createContext, useContext, useState } from 'react';

const ModalContext = createContext();

function Modal({ children, onClose }) {
    const [isOpen, setIsOpen] = useState(false);
    
    const open = () => setIsOpen(true);
    const close = () => {
        setIsOpen(false);
        onClose?.();
    };

    return (
        <ModalContext.Provider value={{ isOpen, open, close }}>
            {children}
        </ModalContext.Provider>
    );
}

function ModalTrigger({ children }) {
    const { open } = useContext(ModalContext);
    return React.cloneElement(children, { onClick: open });
}

function ModalContent({ children }) {
    const { isOpen, close } = useContext(ModalContext);
    
    if (!isOpen) return null;
    
    return (
        <div className="modal-overlay" onClick={close}>
            <div className="modal-content" onClick={e => e.stopPropagation()}>
                <button className="modal-close" onClick={close}>×</button>
                {children}
            </div>
        </div>
    );
}

// Compound exports
Modal.Trigger = ModalTrigger;
Modal.Content = ModalContent;

// Usage
function App() {
    return (
        <Modal>
            <Modal.Trigger>
                <button>Open Modal</button>
            </Modal.Trigger>
            <Modal.Content>
                <h2>Modal Title</h2>
                <p>Modal content goes here</p>
            </Modal.Content>
        </Modal>
    );
}
```

### Render Props Pattern for Flexible Components

```jsx
// components/DataFetcher.jsx - Render props for data fetching
function DataFetcher({ url, children }) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const response = await fetch(url);
                const result = await response.json();
                setData(result);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [url]);

    // Render props pattern - pass state to children function
    return children({ data, loading, error });
}

// Usage with different UI patterns
function UserList() {
    return (
        <DataFetcher url="/api/users">
            {({ data, loading, error }) => {
                if (loading) return <LoadingSpinner />;
                if (error) return <ErrorMessage error={error} />;
                
                return (
                    <ul className="user-list">
                        {data?.map(user => (
                            <li key={user.id}>{user.name}</li>
                        ))}
                    </ul>
                );
            }}
        </DataFetcher>
    );
}
```

---

## Components

### Reusable Components

```jsx
// modules/Home/components/Header.jsx
import React from 'react';

function Header({ title = 'My App', user = null }) {
    return (
        <header className="header">
            <div className="container">
                <h1 className="logo">{title}</h1>
                <nav className="nav">
                    <a href="/">Home</a>
                    <a href="/about">About</a>
                    {user ? (
                        <div className="user-menu">
                            <span>Welcome, {user.name}</span>
                            <a href="/logout">Logout</a>
                        </div>
                    ) : (
                        <a href="/login">Login</a>
                    )}
                </nav>
            </div>
        </header>
    );
}

export default Header;
```

### Data Components

```jsx
// modules/Home/components/DataTable.jsx
import React, { useState } from 'react';

function DataTable({ data, onEdit, onDelete }) {
    const [sortBy, setSortBy] = useState('id');
    const [sortOrder, setSortOrder] = useState('asc');

    const sortedData = [...data].sort((a, b) => {
        if (sortOrder === 'asc') {
            return a[sortBy] > b[sortBy] ? 1 : -1;
        }
        return a[sortBy] < b[sortBy] ? 1 : -1;
    });

    const handleSort = (column) => {
        if (sortBy === column) {
            setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            setSortBy(column);
            setSortOrder('asc');
        }
    };

    return (
        <div className="data-table">
            <table>
                <thead>
                    <tr>
                        <th onClick={() => handleSort('id')}>
                            ID {sortBy === 'id' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th onClick={() => handleSort('name')}>
                            Name {sortBy === 'name' && (sortOrder === 'asc' ? '↑' : '↓')}
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {sortedData.map(item => (
                        <tr key={item.id}>
                            <td>{item.id}</td>
                            <td>{item.name}</td>
                            <td>
                                <button onClick={() => onEdit(item)}>Edit</button>
                                <button onClick={() => onDelete(item.id)}>Delete</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default DataTable;
```

---

## Performance Optimization

### Code Splitting with React.lazy

Split your application into smaller chunks for better loading performance:

```jsx
// app.jsx - Lazy loading components
import React, { Suspense } from 'react';
import { createRoot } from 'react-dom/client';
import ErrorBoundary from './components/ErrorBoundary';

// Lazy load heavy components
const Dashboard = React.lazy(() => import('./components/Dashboard'));
const Analytics = React.lazy(() => import('./components/Analytics'));
const Settings = React.lazy(() => import('./components/Settings'));

function App() {
    const [currentView, setCurrentView] = useState('dashboard');

    const renderView = () => {
        switch (currentView) {
            case 'dashboard':
                return <Dashboard />;
            case 'analytics':
                return <Analytics />;
            case 'settings':
                return <Settings />;
            default:
                return <Dashboard />;
        }
    };

    return (
        <ErrorBoundary>
            <div className="app">
                <Navigation onViewChange={setCurrentView} />
                <main className="main-content">
                    <Suspense fallback={<div className="loading">Loading...</div>}>
                        {renderView()}
                    </Suspense>
                </main>
            </div>
        </ErrorBoundary>
    );
}
```

### Memoization for Performance

Use React.memo and useMemo to prevent unnecessary re-renders:

```jsx
// components/UserList.jsx - Optimized list component
import React, { memo, useMemo } from 'react';

const UserCard = memo(function UserCard({ user, onEdit, onDelete }) {
    return (
        <div className="user-card">
            <img src={user.avatar} alt={user.name} />
            <h3>{user.name}</h3>
            <p>{user.email}</p>
            <div className="actions">
                <button onClick={() => onEdit(user)}>Edit</button>
                <button onClick={() => onDelete(user.id)}>Delete</button>
            </div>
        </div>
    );
});

function UserList({ users, searchTerm, onEdit, onDelete }) {
    // Memoize filtered users to prevent recalculation on each render
    const filteredUsers = useMemo(() => {
        if (!searchTerm) return users;
        
        return users.filter(user =>
            user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            user.email.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [users, searchTerm]);

    // Memoize sorted users
    const sortedUsers = useMemo(() => {
        return [...filteredUsers].sort((a, b) => a.name.localeCompare(b.name));
    }, [filteredUsers]);

    return (
        <div className="user-list">
            {sortedUsers.map(user => (
                <UserCard
                    key={user.id}
                    user={user}
                    onEdit={onEdit}
                    onDelete={onDelete}
                />
            ))}
        </div>
    );
}

export default memo(UserList);
```

### Virtual Scrolling for Large Lists

Handle large datasets efficiently:

```jsx
// components/VirtualList.jsx - Virtual scrolling implementation
import React, { useState, useEffect, useRef, useMemo } from 'react';

function VirtualList({ 
    items, 
    itemHeight = 50, 
    containerHeight = 400,
    renderItem 
}) {
    const [scrollTop, setScrollTop] = useState(0);
    const containerRef = useRef();

    // Calculate visible range
    const visibleRange = useMemo(() => {
        const start = Math.floor(scrollTop / itemHeight);
        const visibleCount = Math.ceil(containerHeight / itemHeight);
        const end = Math.min(start + visibleCount + 1, items.length);
        
        return { start, end };
    }, [scrollTop, itemHeight, containerHeight, items.length]);

    // Calculate total height and visible items
    const totalHeight = items.length * itemHeight;
    const visibleItems = items.slice(visibleRange.start, visibleRange.end);

    const handleScroll = (e) => {
        setScrollTop(e.target.scrollTop);
    };

    return (
        <div
            ref={containerRef}
            className="virtual-list"
            style={{ height: containerHeight, overflow: 'auto' }}
            onScroll={handleScroll}
        >
            <div style={{ height: totalHeight, position: 'relative' }}>
                <div
                    style={{
                        transform: `translateY(${visibleRange.start * itemHeight}px)`,
                        position: 'absolute',
                        width: '100%'
                    }}
                >
                    {visibleItems.map((item, index) => (
                        <div
                            key={visibleRange.start + index}
                            style={{ height: itemHeight }}
                        >
                            {renderItem(item, visibleRange.start + index)}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

// Usage
function LargeDataList({ data }) {
    return (
        <VirtualList
            items={data}
            itemHeight={60}
            containerHeight={500}
            renderItem={(item, index) => (
                <div className="list-item">
                    <h4>{item.title}</h4>
                    <p>{item.description}</p>
                </div>
            )}
        />
    );
}
```

---

## State Management

### Using React Hooks

```jsx
// modules/Dashboard/hooks/useData.js
import { useState, useEffect } from 'react';

export function useData(endpoint) {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchData();
    }, [endpoint]);

    const fetchData = async () => {
        try {
            setLoading(true);
            const response = await fetch(endpoint);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            setData(result.data || result);
            setError(null);
        } catch (err) {
            setError(err.message);
            setData([]);
        } finally {
            setLoading(false);
        }
    };

    const refresh = () => fetchData();

    return { data, loading, error, refresh };
}
```

### Context for Global State

```jsx
// modules/Dashboard/contexts/AppContext.jsx
import React, { createContext, useContext, useReducer } from 'react';

const AppContext = createContext();

const initialState = {
    user: null,
    theme: 'light',
    notifications: []
};

function appReducer(state, action) {
    switch (action.type) {
        case 'SET_USER':
            return { ...state, user: action.payload };
        case 'SET_THEME':
            return { ...state, theme: action.payload };
        case 'ADD_NOTIFICATION':
            return { 
                ...state, 
                notifications: [...state.notifications, action.payload] 
            };
        case 'REMOVE_NOTIFICATION':
            return {
                ...state,
                notifications: state.notifications.filter(n => n.id !== action.payload)
            };
        default:
            return state;
    }
}

export function AppProvider({ children }) {
    const [state, dispatch] = useReducer(appReducer, initialState);

    return (
        <AppContext.Provider value={{ state, dispatch }}>
            {children}
        </AppContext.Provider>
    );
}

export function useApp() {
    const context = useContext(AppContext);
    if (!context) {
        throw new Error('useApp must be used within AppProvider');
    }
    return context;
}
```

---

## API Integration

### API Service

```jsx
// modules/shared/services/api.js
class ApiService {
    constructor(baseURL = '/api/v1') {
        this.baseURL = baseURL;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

---

## Testing React Applications

### Unit Testing with Jest and React Testing Library

Modern React testing focuses on testing user behavior rather than implementation details:

```jsx
// __tests__/components/UserCard.test.jsx
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { jest } from '@jest/globals';
import UserCard from '../components/UserCard';

// Mock the API module
jest.mock('../services/api', () => ({
    updateUser: jest.fn(),
    deleteUser: jest.fn()
}));

describe('UserCard Component', () => {
    const mockUser = {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        avatar: '/avatar.jpg'
    };

    const mockProps = {
        user: mockUser,
        onEdit: jest.fn(),
        onDelete: jest.fn()
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    test('renders user information correctly', () => {
        render(<UserCard {...mockProps} />);
        
        expect(screen.getByText('John Doe')).toBeInTheDocument();
        expect(screen.getByText('john@example.com')).toBeInTheDocument();
        expect(screen.getByRole('img')).toHaveAttribute('src', '/avatar.jpg');
    });

    test('calls onEdit when edit button is clicked', () => {
        render(<UserCard {...mockProps} />);
        
        fireEvent.click(screen.getByText('Edit'));
        expect(mockProps.onEdit).toHaveBeenCalledWith(mockUser);
    });

    test('calls onDelete when delete button is clicked', async () => {
        render(<UserCard {...mockProps} />);
        
        fireEvent.click(screen.getByText('Delete'));
        
        await waitFor(() => {
            expect(mockProps.onDelete).toHaveBeenCalledWith(mockUser.id);
        });
    });
});
```

### Integration Testing with Context

Test components that use React Context:

```jsx
// __tests__/hooks/useAuth.test.jsx
import React from 'react';
import { renderHook, act } from '@testing-library/react';
import { AppProvider } from '../contexts/AppContext';
import { useAuth } from '../hooks/useAuth';

// Wrapper component for testing hooks with context
const wrapper = ({ children }) => (
    <AppProvider>{children}</AppProvider>
);

describe('useAuth Hook', () => {
    test('login sets user in context', async () => {
        const { result } = renderHook(() => useAuth(), { wrapper });
        
        const mockUser = { id: 1, name: 'John Doe' };
        
        // Mock fetch response
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({
                    success: true,
                    user: mockUser,
                    token: 'fake-token'
                })
            })
        );

        await act(async () => {
            const success = await result.current.login({
                email: 'john@example.com',
                password: 'password'
            });
            expect(success).toBe(true);
        });

        expect(result.current.user).toEqual(mockUser);
        expect(localStorage.getItem('token')).toBe('fake-token');
    });

    test('logout clears user from context', () => {
        const { result } = renderHook(() => useAuth(), { wrapper });
        
        act(() => {
            result.current.logout();
        });

        expect(result.current.user).toBeNull();
        expect(localStorage.getItem('token')).toBeNull();
    });
});
```

### End-to-End Testing with Cypress

Test complete user workflows:

```javascript
// cypress/integration/user-management.spec.js
describe('User Management', () => {
    beforeEach(() => {
        // Login before each test
        cy.login('admin@example.com', 'password');
        cy.visit('/dashboard/users');
    });

    it('should display user list', () => {
        cy.get('[data-testid="user-list"]').should('be.visible');
        cy.get('[data-testid="user-card"]').should('have.length.greaterThan', 0);
    });

    it('should create new user', () => {
        cy.get('[data-testid="add-user-btn"]').click();
        cy.get('[data-testid="user-form"]').should('be.visible');
        
        cy.get('input[name="name"]').type('New User');
        cy.get('input[name="email"]').type('newuser@example.com');
        cy.get('input[name="password"]').type('password123');
        
        cy.get('button[type="submit"]').click();
        
        cy.get('[data-testid="success-message"]').should('contain', 'User created successfully');
        cy.get('[data-testid="user-list"]').should('contain', 'New User');
    });

    it('should edit existing user', () => {
        cy.get('[data-testid="user-card"]').first().within(() => {
            cy.get('[data-testid="edit-btn"]').click();
        });
        
        cy.get('[data-testid="user-form"]').should('be.visible');
        cy.get('input[name="name"]').clear().type('Updated Name');
        cy.get('button[type="submit"]').click();
        
        cy.get('[data-testid="success-message"]').should('contain', 'User updated successfully');
    });
});
```

---

## Deployment and Production

### Build Optimization

Configure Vite for production builds:

```javascript
// vite.config.js - Production configuration
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig(({ command, mode }) => {
    const isDev = command === 'serve';
    
    return {
        plugins: [react()],
        
        build: {
            minify: 'terser',
            sourcemap: isDev,
            rollupOptions: {
                input: {
                    afaq: resolve(__dirname, 'modules/Afaq/app.jsx'),
                    app: resolve(__dirname, 'modules/App/app.jsx'),
                },
                output: {
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash].[ext]',
                    manualChunks: {
                        vendor: ['react', 'react-dom'],
                        utils: ['lodash', 'date-fns']
                    }
                }
            },
            terserOptions: {
                compress: {
                    drop_console: !isDev,
                    drop_debugger: !isDev
                }
            }
        },
        
        define: {
            'process.env.NODE_ENV': JSON.stringify(mode),
            __DEV__: isDev
        }
    };
});
```

### Error Boundaries for Production

Implement error boundaries to handle runtime errors gracefully:

```jsx
// components/ErrorBoundary.jsx - Production error handling
import React from 'react';

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null, errorInfo: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error('ErrorBoundary caught an error:', error, errorInfo);
        
        // Log to error reporting service in production
        if (process.env.NODE_ENV === 'production') {
            this.logErrorToService(error, errorInfo);
        }
        
        this.setState({
            error: error,
            errorInfo: errorInfo
        });
    }

    logErrorToService(error, errorInfo) {
        // Send error to monitoring service (e.g., Sentry, LogRocket)
        fetch('/api/errors', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: error.message,
                stack: error.stack,
                componentStack: errorInfo.componentStack,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href
            })
        });
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="error-boundary">
                    <h2>Something went wrong</h2>
                    <p>We're sorry, but something unexpected happened.</p>
                    {process.env.NODE_ENV === 'development' && (
                        <details style={{ whiteSpace: 'pre-wrap' }}>
                            <summary>Error Details (Development Only)</summary>
                            {this.state.error && this.state.error.toString()}
                            <br />
                            {this.state.errorInfo.componentStack}
                        </details>
                    )}
                    <button onClick={() => window.location.reload()}>
                        Reload Page
                    </button>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
```

### Progressive Web App (PWA) Features

Add PWA capabilities for better user experience:

```javascript
// public/sw.js - Service Worker for offline capability
const CACHE_NAME = 'app-v1';
const urlsToCache = [
    '/',
    '/build/assets/app.css',
    '/build/assets/app.js',
    '/api/user/profile' // Cache critical API calls
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});
```

```jsx
// hooks/useServiceWorker.js - Service Worker registration
import { useEffect } from 'react';

export function useServiceWorker() {
    useEffect(() => {
        if ('serviceWorker' in navigator && process.env.NODE_ENV === 'production') {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    }, []);
}
```

This comprehensive documentation now explains the internal workings of React SPA development with the PHP backend, covering modern patterns, performance optimization, testing strategies, and production deployment considerations.

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    // HTTP methods
    get(endpoint) {
        return this.request(endpoint);
    }

    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }
}

export default new ApiService();
```

### Using the API Service

```jsx
// modules/Dashboard/components/UserManager.jsx
import React, { useState, useEffect } from 'react';
import api from '../services/api.js';

function UserManager() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadUsers();
    }, []);

    const loadUsers = async () => {
        try {
            setLoading(true);
            const response = await api.get('/users');
            setUsers(response.data);
        } catch (error) {
            console.error('Failed to load users:', error);
        } finally {
            setLoading(false);
        }
    };

    const createUser = async (userData) => {
        try {
            const response = await api.post('/users', userData);
            setUsers([...users, response.data]);
            return response.data;
        } catch (error) {
            console.error('Failed to create user:', error);
            throw error;
        }
    };

    const updateUser = async (id, userData) => {
        try {
            const response = await api.put(`/users/${id}`, userData);
            setUsers(users.map(user => 
                user.id === id ? response.data : user
            ));
            return response.data;
        } catch (error) {
            console.error('Failed to update user:', error);
            throw error;
        }
    };

    const deleteUser = async (id) => {
        try {
            await api.delete(`/users/${id}`);
            setUsers(users.filter(user => user.id !== id));
        } catch (error) {
            console.error('Failed to delete user:', error);
            throw error;
        }
    };

    return (
        <div className="user-manager">
            <h2>User Management</h2>
            {loading ? (
                <div>Loading...</div>
            ) : (
                <UserTable 
                    users={users}
                    onEdit={updateUser}
                    onDelete={deleteUser}
                />
            )}
        </div>
    );
}

export default UserManager;
```

---

## Forms and Validation

### Form Component

```jsx
// modules/shared/components/Form.jsx
import React, { useState } from 'react';

function Form({ onSubmit, initialData = {}, schema }) {
    const [data, setData] = useState(initialData);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    const handleChange = (name, value) => {
        setData(prev => ({ ...prev, [name]: value }));
        
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: null }));
        }
    };

    const validate = () => {
        const newErrors = {};
        
        Object.keys(schema).forEach(field => {
            const rules = schema[field];
            const value = data[field];

            if (rules.required && (!value || value.trim() === '')) {
                newErrors[field] = `${field} is required`;
                return;
            }

            if (rules.minLength && value && value.length < rules.minLength) {
                newErrors[field] = `${field} must be at least ${rules.minLength} characters`;
                return;
            }

            if (rules.email && value && !isValidEmail(value)) {
                newErrors[field] = 'Please enter a valid email address';
                return;
            }
        });

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validate()) {
            return;
        }

        try {
            setSubmitting(true);
            await onSubmit(data);
        } catch (error) {
            console.error('Form submission failed:', error);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="form">
            {Object.keys(schema).map(field => (
                <div key={field} className="form-group">
                    <label>{schema[field].label || field}</label>
                    <input
                        type={schema[field].type || 'text'}
                        value={data[field] || ''}
                        onChange={(e) => handleChange(field, e.target.value)}
                        className={errors[field] ? 'error' : ''}
                    />
                    {errors[field] && (
                        <span className="error-message">{errors[field]}</span>
                    )}
                </div>
            ))}
            <button type="submit" disabled={submitting}>
                {submitting ? 'Submitting...' : 'Submit'}
            </button>
        </form>
    );
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export default Form;
```

---

## Routing (React Router)

### Install React Router

```bash
npm install react-router-dom
```

### Setup Routing

```jsx
// modules/Dashboard/app.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Dashboard from './Dashboard.jsx';
import UserList from './components/UserList.jsx';
import UserEdit from './components/UserEdit.jsx';
import Settings from './components/Settings.jsx';

function App() {
    return (
        <Router basename="/dashboard">
            <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/users" element={<UserList />} />
                <Route path="/users/:id/edit" element={<UserEdit />} />
                <Route path="/settings" element={<Settings />} />
            </Routes>
        </Router>
    );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
```

---

## Build and Development

### Development Commands

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Package.json Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "lint": "eslint modules --ext .js,.jsx",
    "lint:fix": "eslint modules --ext .js,.jsx --fix"
  }
}
```

### Deployment

```bash
# Build production assets
npm run build

# Assets are generated in build/ directory
# Include them in your PHP views like:
# <script src="/build/home/main.js"></script>
```

---

## PHP Integration

### Loading SPAs in PHP Views

```php
<!-- views/template/react_shell.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My App' ?></title>
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div id="root"></div>
    
    <!-- Pass PHP data to React -->
    <script>
        window.APP_DATA = {
            user: <?= json_encode($user ?? null) ?>,
            config: <?= json_encode($config ?? []) ?>,
            csrf_token: '<?= csrf_token() ?>'
        };
    </script>
    
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
```

### Controller Integration

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return $this->view('template/react_shell', [
            'title' => 'Dashboard',
            'user' => $user,
            'config' => [
                'api_url' => config('app.api_url'),
                'version' => config('app.version')
            ],
            'scripts' => ['/build/dashboard/main.js'],
            'styles' => ['/build/dashboard/style.css']
        ]);
    }
}
```

---

## Best Practices

### 1. Component Organization

```
modules/
├── shared/           # Shared components and utilities
│   ├── components/
│   ├── hooks/
│   ├── services/
│   └── utils/
└── [SpaName]/        # Individual SPA modules
    ├── app.jsx       # Entry point
    ├── components/   # SPA-specific components
    ├── hooks/        # Custom hooks
    └── styles/       # CSS files
```

### 2. Code Splitting

```jsx
// Lazy load components
import React, { lazy, Suspense } from 'react';

const UserList = lazy(() => import('./components/UserList.jsx'));
const Settings = lazy(() => import('./components/Settings.jsx'));

function App() {
    return (
        <Suspense fallback={<div>Loading...</div>}>
            <Routes>
                <Route path="/users" element={<UserList />} />
                <Route path="/settings" element={<Settings />} />
            </Routes>
        </Suspense>
    );
}
```

### 3. Error Handling

```jsx
// Error boundary component
class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error('React Error:', error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return <h2>Something went wrong.</h2>;
        }

        return this.props.children;
    }
}
```

### 4. Performance Optimization

```jsx
import React, { memo, useMemo, useCallback } from 'react';

const DataList = memo(({ data, onItemClick }) => {
    const sortedData = useMemo(() => {
        return data.sort((a, b) => a.name.localeCompare(b.name));
    }, [data]);

    const handleClick = useCallback((item) => {
        onItemClick(item.id);
    }, [onItemClick]);

    return (
        <ul>
            {sortedData.map(item => (
                <li key={item.id} onClick={() => handleClick(item)}>
                    {item.name}
                </li>
            ))}
        </ul>
    );
});
```

This setup provides a powerful foundation for building modern React SPAs that integrate seamlessly with your PHP backend.
