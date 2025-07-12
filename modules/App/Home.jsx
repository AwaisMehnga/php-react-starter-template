import React, { useState, lazy, Suspense } from 'react';

// Helper function to add delay to lazy loading
const delayedImport = (importFunc, delay = 2000) => {
    return lazy(() => 
        new Promise(resolve => {
            setTimeout(() => {
                resolve(importFunc());
            }, delay);
        })
    );
};

const Awais = delayedImport(() => import('./components/awais'), 2000);
const Afaq = delayedImport(() => import('./components/afaq'), 3000);
import Shafaqat from './components/shafaqat';
import Home from '../Afaq/Home';

function App() {
    const [count, setCount] = useState(0);

    return (
        <div className="app">
            <header className="app-header">
                <h1>React SPA APP</h1>
                <p>Your React single-page application is ready!</p>
                <div className="counter">
                    <button onClick={() => setCount(count - 1)}>-</button>
                    <span>Count: {count}</span>
                    <button onClick={() => setCount(count + 1)}>+</button>
                </div>
                <p>Edit <code>modules/App/App.jsx</code> to customize this page.</p>
            </header>

            <Suspense fallback={<div>Loading Awais...</div>}>
                <Awais />
            </Suspense>
            <Suspense fallback={<div>Loading Afaq...</div>}>
                <Afaq />
            </Suspense>
            <Shafaqat />
            <Home />
            
        </div>
    );
}

export default App;
