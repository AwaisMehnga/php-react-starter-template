import React, { Suspense, useState, lazy } from "react";
import "./App.css";

const Modal = lazy(() => import("./Modal")); // Lazy-loaded modal

function App() {
  const [showModal, setShowModal] = useState(false);

  return (
    <div className="App">
      <h1>Home Awais</h1>
      <button onClick={() => setShowModal(true)}>Open Modal</button>

      {showModal && (
        <Suspense fallback={<div>Loading modal...</div>}>
          <Modal onClose={() => setShowModal(false)} />
        </Suspense>
      )}
    </div>
  );
}

export default App;
