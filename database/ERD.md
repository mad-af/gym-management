# ERD Gym Management System

```mermaid
erDiagram

%% =========================
%% AUTH
%% =========================
users {
    uuid id PK
    string name
    string email
    string password
    string role
    timestamp created_at
}

%% =========================
%% CUSTOMER
%% =========================
customers {
    uuid id PK
    string name
    string phone
    string email
    string code
    timestamp created_at
}

%% =========================
%% MEMBERSHIP PACKAGE
%% =========================
membership_packages {
    uuid id PK
    string name
    int duration_days`
    decimal price
    string description
    boolean is_active
    timestamp created_at
}

membership_package_items {
    uuid id PK
    uuid package_id FK
    string item_name
    int quantity
    string unit
}

%% =========================
%% MEMBERSHIP TRANSACTION
%% =========================
membership_transactions {
    uuid id PK
    uuid customer_id FK
    uuid package_id FK
    date start_date
    date end_date
    decimal price
    string status "ACTIVE | EXPIRED | CANCELLED"
    uuid created_by FK
    timestamp created_at
}

%% =========================
%% VISITS
%% =========================
visits {
    uuid id PK
    uuid customer_id FK
    uuid membership_transaction_id FK
    string visit_type "MEMBERSHIP | DAILY"
    decimal price
    string checkin_method "QR_CODE | CARD | MANUAL"
    uuid created_by FK
    timestamp checkin_time
}

%% =========================
%% INVENTORY
%% =========================
products {
    uuid id PK
    string name
    decimal price
    int stock
    timestamp created_at
}

stock_movements {
    uuid id PK
    uuid product_id FK
    string type "IN | OUT | ADJUSTMENT"
    int quantity
    decimal cost_price
    string description
    timestamp created_at
}

%% =========================
%% SALES
%% =========================
sales {
    uuid id PK
    uuid customer_id FK
    decimal total_amount
    uuid created_by FK
    timestamp created_at
}

sale_items {
    uuid id PK
    uuid sale_id FK
    uuid product_id FK
    int quantity
    decimal price
    decimal subtotal
}

%% =========================
%% RELATIONSHIPS
%% =========================

customers ||--o{ membership_transactions : has
membership_packages ||--o{ membership_transactions : purchased
membership_packages ||--o{ membership_package_items : contains

customers ||--o{ visits : checkin
membership_transactions ||--o{ visits : used_for

products ||--o{ stock_movements : movement

customers ||--o{ sales : makes
sales ||--o{ sale_items : contains
products ||--o{ sale_items : sold_in

users ||--o{ membership_transactions : creates
users ||--o{ visits : records
users ||--o{ sales : records
```
