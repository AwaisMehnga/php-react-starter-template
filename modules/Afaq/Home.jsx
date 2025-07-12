import React, { useState, lazy } from 'react';
import './style.css';
const Awais = lazy(() => import('./components/awais'));
const Afaq = lazy(() => import('./components/afaq'));
import Shafaqat from './components/shafaqat';
const Modal = lazy(() => import("./Modal1"));
import { Suspense } from 'react';

function Home() {
    const [count, setCount] = useState(0);
    const [showModal, setShowModal] = useState(false);


    return (
        <div className="app">
            <header className="app-header">
                <h1>React </h1>
                <p>Your React single-page application is ready!</p>
                <div className="counter">
                    <button onClick={() => setCount(count - 1)}>-</button>
                    <span>Count: {count}</span>
                    <button onClick={() => setCount(count + 1)}>+</button>
                </div>
                <p>Edit <code>modules/App/App.jsx</code> to customize this page.</p>
            </header>
            <button onClick={() => setShowModal(true)}>Open Modal</button>
            
                  {showModal && (
                    <Suspense fallback={<div>Loading modal...</div>}>
                      <Modal onClose={() => setShowModal(false)} />
                    </Suspense>
                  )}

            <Awais />
            <Afaq />
            <Shafaqat />
            
        </div>
    );
}

export default Home;
