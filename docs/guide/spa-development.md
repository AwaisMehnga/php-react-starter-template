---
layout: default
title: React SPA Development  
nav_order: 6
---

# React SPA Development
{: .no_toc }

Build modern single-page applications with React and Vite integration.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

The template includes a powerful Vite + React setup for building modern SPAs with:
- Hot module replacement (HMR)
- Fast builds and development
- ES6+ module support
- Component-based architecture
- Seamless PHP backend integration

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

        // Add authentication token if available
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

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
