import React from "react";

export default function Modal({ onClose }) {
  return (
    <div className="modal">
      <h2>This is a lazy-loaded modal!</h2>
      <button onClick={onClose}>Close</button>
    </div>
  );
}
