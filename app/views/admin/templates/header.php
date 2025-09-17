<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin | Lunerburg & Co</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --color-bg-body: #ded5c6;
            --color-bg-footer: #424e4e;
            --color-bg-topbar: #ded5c6;
            --color-bg-btn: #f0e8da;
            --color-bg-btn-hover: #e4ddd3;
            --color-btn-text: #46555f;
            --color-btn-text-hover: #3e4a4e;
            --color-link: #333;
            --color-link-hover: #8b9a6d;
            --color-border-card: #e0e0e0;
            --color-offcanvas-bg: #f8f9fa;
            --color-offcanvas-text: #333;
            --color-collection-text: #000;
            --color-badge-bg: #f8b400;
            --color-badge-text: white;
            --color-offcanvas-link-hover: #6c757d;
            --color-bg-dark-brown: #3e2a47;
            --color-primary: #8b9a6d;
            --color-info: #46555f;
            --color-success: #3e4a4e;
            --color-warning: #f8b400;
        }

        body {
            background-color: var(--color-bg-body);
        }

        .sidebar {
            background-color: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding: 20px;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            transform: translateX(0);
            z-index: 1050;
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease-in-out;
            width: 100%;
        }

        .nav-link.active {
            color: var(--color-link-hover) !important;
            background-color: #e9ecef;
            border-left: 3px solid var(--color-link-hover);
        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }

        .card-icon {
            font-size: 2.5rem;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-right: 1.5rem;
        }
        .card .text-muted {
            font-size: 0.85rem;
        }

        .bg-primary-light { background-color: rgba(139, 154, 109, 0.1); }
        .bg-info-light { background-color: rgba(70, 85, 95, 0.1); }
        .bg-success-light { background-color: rgba(62, 74, 78, 0.1); }
        .bg-warning-light { background-color: rgba(248, 180, 0, 0.1); }

        .text-primary-custom { color: var(--color-primary); }
        .text-info-custom { color: var(--color-info); }
        .text-success-custom { color: var(--color-success); }
        .text-warning-custom { color: var(--color-warning); }
    </style>
</head>
<body>
    <div class="d-flex">