body {
  font-family: 'Segoe UI', Tahoma, sans-serif;
  background: #f7f7fb;
  color: #1f2937;
}

.text-pink { color: #ff5ca8 !important; }
.btn-pink {
  background: linear-gradient(135deg, #ff7bb5, #ff4fa0);
  border: none;
  color: #fff;
}
.btn-pink:hover { color: #fff; filter: brightness(0.96); }

.topnav {
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.sidebar {
  width: 250px;
  min-height: calc(100vh - 60px);
  background: #fff;
  border-right: 1px solid #f1f1f1;
}

.brand-box {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #ff5ca8;
  padding: 12px 14px;
  border-radius: 12px;
  background: #fff5fa;
}

.sidebar .nav-link {
  color: #495057;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.sidebar .nav-link:hover {
  background: #fff4fa;
  color: #ff5ca8;
}

.content-area {
  flex: 1;
  min-height: calc(100vh - 60px);
}

.summary-card {
  background: linear-gradient(180deg, #fff, #fff7fb);
  border: 1px solid #f4dfe9;
  border-radius: 18px;
  box-shadow: 0 10px 25px rgba(32, 37, 64, 0.04);
}

.summary-card small {
  color: #6c757d;
}

.summary-card h4 {
  color: #1a1d29;
  margin-bottom: 0;
}

.card {
  border-radius: 18px;
}

.table thead th {
  background: #f8f9fa;
}

@media (max-width: 767px) {
  .sidebar {
    width: 100%;
    min-height: auto;
  }
  .d-flex {
    display: block !important;
  }
  .content-area {
    padding-top: 1rem !important;
  }
}

@media print {
  .sidebar, .navbar, .btn, .d-flex.justify-content-between .d-flex.gap-2 {
    display: none !important;
  }
  .content-area {
    width: 100%;
    max-width: none;
    padding: 0 !important;
  }
}
