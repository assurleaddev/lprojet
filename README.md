# 👗 USED

A modern, robust, and community-driven marketplace platform for buying and selling second-hand clothes. Built with Laravel 12, Livewire 3, and Tailwind CSS 4, **USED** provides a seamless experience for fashion enthusiasts to give clothes a second life.

**Developed By:** [Mohammed benouijem](mailto:benouijemmed@gmail.com)

---

## 🌟 Key Features

### 🛍️ For Sellers
- **Easy Listing**: Quickly upload and manage your pre-loved clothes with multi-level categories (Top, Bottom, Outerwear, etc.).
- **Attribute Management**: Define specific details like size, color, material, and condition.
- **Brand Registry**: Organize listings by popular or custom brands.
- **Inventory Tracking**: Manage stock levels and product variations effortlessly.

### 💸 Financials & Security
- **Integrated Digital Wallet**: Securely manage earnings, balances, and transactions within the platform.
- **Commission Management**: Automatic platform commission calculation for every successful sale.
- **Reviews & Ratings**: Build trust in the community with a detailed feedback system.

### 💬 Community & Communication
- **Real-time Chat**: Direct communication between buyers and sellers using integrated Livewire and Pusher messaging.
- **User Profiles**: Personalize your shop with custom avatars and bio.
- **Follow System**: Stay updated with your favorite sellers.

### 📦 Logistics & Support
- **Shipping Integration**: Configurable shipping options with automated fee management.
- **Order Lifecycle**: Complete tracking from "Order Placed" to "Delivered".
- **Refunds & Disputes**: Structured system for handling transaction issues.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Livewire 3.x, Alpine.js, React (for specific interactive modules)
- **Styling**: Tailwind CSS 4.x
- **Real-time**: Pusher
- **Database**: MySQL / PostgreSQL
- **Monitoring**: Laravel Pulse, Action Logs

---

## 📋 Requirements

- PHP ^8.2 | 8.3 | 8.4
- Node.js ^20.x
- Composer ^2.x

---

## 🚀 Project Setup

1. **Clone the repository**
   ```console
   git clone [repository-url]
   cd used
   ```

2. **Install dependencies**
   ```console
   composer install
   npm install
   ```

3. **Configure Environment**
   - Create a `.env` file from `.env.example`.
   - Update database credentials and Pusher/Twilio settings.

4. **Initialize Application**
   ```console
   php artisan key:generate
   php artisan storage:link
   php artisan migrate:fresh --seed
   ```

5. **Run the application**
   ```console
   npm run dev
   ```

The platform will be available at `http://localhost:8000`.

---

## ⚙️ How it works

1. **Discovery**: Users can browse unique items through an optimized SEO-friendly catalog.
2. **Interaction**: Buyers can chat with sellers in real-time to ask about sizing or negotiate.
3. **Transaction**: Payments are handled securely, with funds held in the digital wallet system.
4. **Fulfillment**: Sellers ship items using pre-configured shipping methods, and buyers track their arrival.
5. **Community Trust**: Every transaction ends with a review, ensuring a safe and reliable experience for everyone.

---

## 🧩 Core Architecture

The platform is built on a modular architecture, allowing for easy extension:
- **Chat Module**: Handles all real-time messaging.
- **Wallet Module**: Manages the ledger and user balances.
- **Product Module**: Core e-commerce logic for clothes listings.
- **Order Module**: Manages the checkout and fulfillment flow.

---

