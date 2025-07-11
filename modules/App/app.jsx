import React, { useState } from 'react';

function App() {
    const [count, setCount] = useState(0);

    return (
        <div className="app">
            <header className="app-header">
                <h1>React SPA</h1>
                <p>Your React single-page application is ready!</p>
                <div className="counter">
                    <button onClick={() => setCount(count - 1)}>-</button>
                    <span>Count: {count}</span>
                    <button onClick={() => setCount(count + 1)}>+</button>
                </div>
                <p>Edit <code>modules/App/App.jsx</code> to customize this page.</p>
            </header>
        </div>
    );
}

export default App;
