import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';

const mountNode = document.getElementById('react-property-root');
if (mountNode) {
  const root = ReactDOM.createRoot(mountNode);
  root.render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  );
}
