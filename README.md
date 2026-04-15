# 🚀 Z-STORE | Multi-Vendor E-Commerce Platform

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.0-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-Modern_Monolith-9553E9?style=for-the-badge&logo=inertia&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5.4.1-FBBF24?style=for-the-badge&logo=filament&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![TiDB](https://img.shields.io/badge/TiDB-Serverless-444444?style=for-the-badge&logo=tidb&logoColor=white)

Z-STORE is a modern, scalable Multi-Vendor E-Commerce platform built with the Laravel-Vue-Inertia stack. Designed to handle complex marketplace operations, it features a unique multi-origin shipping calculation logic, unified payments, real-time messaging, and dedicated seller management.

## ✨ Key Features & Engineering Highlights

- 🛒 **Multi-Vendor Cart Architecture:** Intelligently groups cart items by `store_id`, allowing buyers to purchase from multiple different sellers in a single transaction.
- 📦 **Dynamic Multi-Shipping (Biteship API):** Automatically maps and loops individual origin-destination logistics. Buyers can select different couriers for different stores within the same checkout page.
- 💳 **Unified Payment Gateway (Midtrans):** Aggregates total item costs and multi-store shipping fees into a single Midtrans Snap Token for a seamless user experience.
- 💬 **Real-time Chat:** Integrated real-time communication between buyers and sellers using Pusher/WebSockets with instant UI updates.
- 📄 **Thermal-Ready PDF Generation:** Automated shipping label generator (via DomPDF) mapped specifically to individual `OrderStore` sub-invoices, formatted perfectly for 10x15cm thermal printers.
- 🏢 **Independent Seller Dashboard:** Sellers have restricted access to manage only their store's orders, input tracking numbers, and view specific product reviews.

## 🛠️ Tech Stack

**Frontend:**

- Vue.js 3 (Composition API)
- Inertia.js (Modern Monolith approach)
- Tailwind CSS (Styling)

**Backend:**

- Laravel 13
- Filament Admin v5.4.1 (For Admin/Seller Panels)
- MySQL / TiDB Serverless (Cloud Database)

**Third-Party Services & Infrastructure:**

- **Midtrans:** Payment Gateway integration
- **Biteship:** Courier & Logistics API
- **Pusher:** WebSocket broadcasting for Real-time chat
- **Docker:** Containerized deployment
- **Render:** Cloud Application Hosting

## 🚀 Live Demo

> https://z-store-a9vl.onrender.com/

## 💻 Local Setup & Installation

To run this project locally, ensure you have PHP, Composer, Node.js, and a database server installed.

**1. Clone the repository**

```bash
git clone [https://github.com/yourusername/z-store.git](https://github.com/yourusername/z-store.git)
cd z-store
```
