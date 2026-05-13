# CT Inventory

A Laravel 11 product inventory manager. Add products via a form and see live totals — no database setup required.

## Features

- Add products: name, quantity in stock, price per item
- Data stored in `storage/app/products.json`
- Table sorted by datetime submitted
- Total Value column (quantity × price) with a grand total row
- Inline edit per row — Edit, Save, or Cancel without leaving the page
- AJAX form submission via Axios — no page reloads

## Requirements

- PHP 8.2+
- Composer

## Quick Start

```bash
git clone https://github.com/kuddusclank/ct-inventory.git
cd ct-inventory

composer install
cp .env.example .env
php artisan key:generate

php artisan serve
```

Open `http://127.0.0.1:8000` in your browser.

## Stack

- Laravel 11
- Bootstrap 5 via CDN
- Axios via CDN
- JSON file storage (no database)
