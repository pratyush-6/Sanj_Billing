# Sanjeevani Business Management System
## Current System Specification (as-built)

> This document was originally written as a forward-looking implementation plan for an expense/accounting tool. It has since been rewritten to describe the system **as it actually exists in code today**, and is kept as the source of truth for scope and architecture. Sections describing features that are not yet built are explicitly marked **(Not Built — Future)**.

---

# 1. Project Overview

## Project Name

Sanjeevani Business Management System (originally scoped as "Sanjeevani Expense, Accounting & Tax Management System"; the scope has grown into a full multi-company Vyapar-style business platform).

## Purpose

The system gives Sanjeevani (and any additional companies onboarded into the same install) a centralized platform to:

- Record all business expenses, categorized and vendor-linked.
- Maintain a unified Party master for vendors, customers, or both.
- Manage inventory: products, categories, stock levels, weighted-average costing.
- Run a full **Procurement** cycle: Vendor Quotation → Approval → Purchase Order → Goods Receipt → Purchase Bill → Payment.
- Run a full **Sales** cycle: Sale Order → Delivery Challan → Sale Invoice → Payment.
- Print GST-compliant Purchase Bills, Sale Invoices and Delivery Challans as PDFs.
- Track GST (CGST/SGST/IGST, computed from company/party state) and TDS (configurable sections/rates) on both purchase and sale sides.
- Maintain full double-entry accounting: Chart of Accounts, Journal, Ledger, Trial Balance, Profit & Loss, Balance Sheet.
- Maintain a derived Party Ledger (outstanding receivable/payable per party, financial statement, and item-wise product transaction history).
- Operate across **multiple companies** from one login, with per-company data isolation.
- Support light/dark theme.
- Maintain a Calendar / Daily Notes module for operational reminders.
- Maintain a full audit trail and never hard-delete financial records.

Fixed Assets/Depreciation, CA/ITR export tooling, and automation (OCR, bank import, recurring expenses) remain future scope — see Section 31.

---

# 2. Main Business Objective

The system should answer, at any time:

1. How much did the business spend, and where?
2. Which vendor was paid, how much, and by what method?
3. Which customer was billed, how much, and how much do they still owe?
4. What is currently in stock, and what did it cost (weighted average)?
5. What purchase orders are open, partially received, or closed?
6. What delivery challans are undelivered, partially invoiced, or fully invoiced?
7. Is GST/TDS applicable on a given transaction, and how was it split?
8. What is the cash/bank balance, and what is the net profit?
9. What does the trial balance, P&L, and balance sheet look like for the active financial year?
10. What does a given party (vendor or customer) currently owe, or is owed?
11. What happened to a given record historically (who created/edited/cancelled it, and when)?

---

# 3. Application Modules (current)

1. Authentication (Laravel Breeze-based)
2. Company Management (multi-company, switchable per session)
3. Financial Year
4. Dashboard
5. Expense Categories & Sub-Categories
6. Parties (unified Vendor/Customer master)
7. Expenses
8. Payments (bidirectional: In from customers, Out to vendors)
9. Bank & Cash Accounts
10. Documents (generic file attachment, polymorphic)
11. Masters: Units, Payment Methods, GST Rate Master, TDS Section Master
12. Inventory: Products, Product Categories, Stock Adjustments, Stock Movements
13. Procurement: Vendor Quotations, Quotation Approvals, Purchase Orders, Goods Receipts, Purchase Bills
14. Sales: Sale Orders, Delivery Challans, Sale Invoices
15. Party Ledger
16. Accounting: Chart of Accounts, Journal, Ledger, Trial Balance, Profit & Loss, Balance Sheet
17. Reports
18. Calendar / Daily Notes
19. Users & Roles/Permissions
20. Audit Logs
21. Settings (Company profile, Financial Years, theme)

Not built: Fixed Assets, CA Dashboard/Export Package, ITR Data Preparation, Recurring Expenses, OCR, Bank Import, Mobile app. See Section 31.

---

# 4. Roles & Permissions

Roles (unchanged from the original plan):

- **Super Admin** — every permission in the system.
- **Admin** — everything except a few Super-Admin-only bits (roughly: everything Super Admin has).
- **Accountant** — day-to-day operational permissions: parties, expenses, inventory, procurement, sales, accounting view, daily notes. No `stock-adjustments.approve` or `vendor-quotations.approve` (self-approval is not allowed).
- **CA** — read-only: `expenses.view`, `reports.view`, `accounting.view`, `daily-notes.view`, `inventory.view`.
- **Viewer** — narrower read-only: `expenses.view`, `reports.view`, `daily-notes.view`, `inventory.view`.

Permission names actually enforced in code (`spatie/laravel-permission`, guard `web`):

    companies.manage, financial-years.manage, users.manage, roles.manage, settings.manage,
    audit-logs.view, expense-categories.manage, parties.manage, bank-accounts.manage,
    masters.manage, expenses.manage, expenses.view, reports.view, accounting.view,
    daily-notes.view, daily-notes.manage, product-categories.manage, products.manage,
    inventory.view, vendor-quotations.manage, vendor-quotations.approve,
    purchase-orders.manage, goods-receipts.manage, purchase-bills.manage,
    sale-orders.manage, delivery-challans.manage, sale-invoices.manage,
    stock-adjustments.manage, stock-adjustments.approve

Every new permission must be added both to `RolePermissionSeeder::$names` **and** granted to the relevant roles, then the seeder must be re-run (`php artisan db:seed --class=RolePermissionSeeder --force`) — a seeder change alone does not retroactively grant it to existing users' cached permissions.

Read access to most modules (Inventory, Procurement, Sales) is a single `inventory.view` permission; write access is a per-module `*.manage` permission. Party/Party Ledger reuses `parties.manage` for both read and write (no separate view tier exists there yet).

---

# 5. Design Principles

## 5.1 Dynamic Categories / Masters

Expense categories, product categories, units, payment methods, GST rates, and TDS sections are all admin-managed master data, never hardcoded. No code change is required to add a new one.

## 5.2 Single Entry Principle

A transaction is entered once and every downstream view (reports, ledgers, dashboards) is *derived* from it, not duplicated into separate tables. Stock levels, account balances, trial balance, and party outstanding balances are all computed live from movement/journal/document tables — never stored as a maintained running total.

## 5.3 Financial Year Based System

All transactional records belong to a `financial_years` row. A financial year can be Active, Locked (blocks new postings), or Closed. Not hardcoded — managed per company.

## 5.4 Multi-Company / Multi-Tenancy

Every transactional table is scoped by `company_id`. A user can belong to multiple companies (`company_user` pivot) and switches the active company via a session-stored `current_company_id` (see `CompanyContextService`). All controllers resolve the active company through `current_company()` / `current_company_or_fail()` and every cross-model query is scoped to it — a record belonging to another company 404s rather than leaking.

## 5.5 Auditability / Never Hard-Delete

Financial and workflow records are never physically deleted. Every document type has a status lifecycle (Draft → Posted/Completed/Confirmed → Cancelled, etc.) and corrections happen via a new offsetting record (a reversing Journal Entry, a Stock Adjustment, a cancelled Payment) rather than editing history. Every meaningful action is written to `audit_logs` via `AuditLogService`.

## 5.6 Derive, Don't Store

Wherever a number can be computed from its source transactions, it is computed on read, not maintained as a stored running total: stock-on-hand, weighted-average cost, account balances, trial balance, P&L/balance sheet figures, and party outstanding balances are all derived live.

## 5.7 Services Own Business Logic

Controllers stay thin; all business rules, calculations, and multi-step transactions live in `app/Services/*Service.php` classes, wrapped in `DB::transaction()` where more than one write must succeed or fail together. Blade views contain no accounting logic.

---

# 6. Core Workflows

## 6.1 Expense Workflow

    Expense Happens → Enter Expense → Category/Sub-Category → Vendor (optional)
    → Quantity/Rate or Fixed Amount → GST/TDS if applicable → Payment Mode
    → Bank/Cash Account → Attach Document → Save (Draft/Approved)
    → Journal Entry posted on Approved → Reports → Financial Year Summary

## 6.2 Procurement Workflow

    Vendor Quotation (Draft) → Submit → Approve/Reject (self-approval blocked)
    → Purchase Order (from an Approved quotation, or created directly) → Send to Vendor
    → Goods Receipt (Draft, editable) → Complete (stock moves in at the PO's locked
       unit price; PO status recomputes to Partially Received / Received)
    → Purchase Bill (created from exactly one Completed, unbilled Goods Receipt;
       line price is locked to the GRN's price) → Post (journal entry: Dr Inventory,
       Dr Input GST, Cr Accounts Payable, Cr TDS Payable if applicable)
    → Payment (Out) against the bill → amountDue() derived as total − TDS − paid

## 6.3 Sales Workflow

    Sale Order (Draft) → Confirm (no approval gate — an order taken from a customer,
       not an internal spending commitment)
    → Delivery Challan (direct, or converted from a Confirmed Sale Order)
       → Complete (stock moves out here — the sole point stock leaves for the
          challan path; challan becomes immutable once Completed)
    → Sale Invoice — EITHER:
        (a) direct sale, no challan: invoice creation itself posts the stock-out, or
        (b) from one or more Delivery Challan lines: stock already moved at
            challan-completion, invoice only bills it (over-invoicing a challan
            line is rejected server-side; challan status auto-recomputes to
            Partially Invoiced / Invoiced)
       — never mixed within one invoice.
    → Post (journal entry: Dr Accounts Receivable, Dr TDS Receivable if applicable,
       Cr Sales, Cr GST Payable, plus Dr COGS / Cr Inventory sized from the
       weighted-average cost snapshotted on each line)
    → Payment (In) from the customer → amountDue() derived as total − TDS − paid

---

# 7. Company Module

## Company Fields (as built)

- Name, Legal Name, Business Type, PAN, TAN, GSTIN, CIN
- Address, State (constrained to `config('india.states')`, 37 GST state/UT codes — not free text), City, Pincode
- Email, Phone, Website, Business Description, Status

## Multi-Company

- A user can be linked to any number of companies (`company_user` pivot table).
- `Route::post('/companies/{company}/switch')` swaps the session's active company.
- `CompanyContextService::current()` defaults to the user's alphabetically-first accessible company if none is set in session.
- A Super Admin implicitly has access to every company; other roles only to companies they're explicitly linked to.

Business Types: Proprietorship, Partnership, LLP, Private Limited, Public Limited, Individual, Other.

---

# 8. Financial Year Module

- Create, Activate (exactly one active FY per company at a time), Lock, Unlock, Close.
- A locked financial year blocks new stock movements and new postings (`StockMovementService`/accounting checks `financial_year->is_locked`).
- Every transactional table carries `financial_year_id`.

---

# 9. Party Module (Vendor + Customer unified)

Originally a separate `Vendor` model; migrated (`rename_vendors_table_to_parties`) into a single `Party` model with `is_vendor` and `is_customer` booleans — a party can be either, or both, with one shared ledger.

## Party Fields

Name, Company Name, Contact Person, Mobile, Email, Address, State (india.php list), City, Pincode, GSTIN, PAN, Bank Name, Account Number, IFSC, Payment Terms, Status, `is_vendor`, `is_customer`.

## Party Features

- Add / Edit / View Party (`parties.manage`)
- Vendor Product Pricing (`vendor_products` — price/lead-time/preferred per product, vendor-only)
- Party Ledger: outstanding balance, chronological statement, item-wise product history (Section 20)
- Expense history, Purchase Bill history, Sale Invoice history, Payment history — all visible from the party's own pages

`purchase_orders`, `vendor_quotations`, and `vendor_products` keep their `vendor_id` column name deliberately (they are genuinely vendor-only interactions); every newer table (`sale_orders`, `delivery_challans`, `sale_invoices`, `purchase_bills`, `payments`) uses `party_id`.

---

# 10. Expense Module

## Expense Fields

Expense Number (auto), Expense Date, Financial Year, Category, Sub Category, Vendor (Party), Description, Quantity, Unit, Rate, Taxable Amount, Discount, GST Amount, TDS Section/Amount, Total Amount, Business/Personal split (Business Amount + Personal Amount), Payment Mode, Payment Account, Invoice Number, Invoice Date, Expense Nature, Notes, Status.

## Expense Nature

Direct, Indirect, Operating, Administrative, Financial, Capital, Personal/Non-business, Other.

## Status

Draft → Approved → (Cancelled). An Approved expense posts a journal entry (`AccountingService::record(Expense $expense)`); cancelling reverses it if one exists.

## Business / Personal Classification

Business, Personal, or Mixed — a Mixed expense splits `total_amount` into `business_amount` + `personal_amount`; only the business portion contributes to accounting/tax-facing figures via `drawings` for the personal portion.

---

# 11. Payment Module

A single `Payment` model settles exactly one document — a `PurchaseBill` (direction `Out`) or a `SaleInvoice` (direction `In`) — via a polymorphic `source_type`/`source_id`. `PaymentService::create()` is fully source-agnostic (works against any model exposing an `amountDue()` method) and needed zero changes when the Sale side was added. A payment cannot exceed the source document's derived `amountDue()`. Cancelling a payment reverses its journal entry.

Fields: Payment Number (auto, `PAY-<FY>-00001`), Direction (In/Out), Amount, Payment Date, Bank Account, Payment Method, Reference Number, Notes, Status (Posted/Cancelled).

A document with `SUM(payments) > 0` cannot be cancelled until its payments are reversed first.

**Not built (V1 limitation, deliberate):** on-account/advance payments later allocated across multiple invoices. A Payment settles one document only.

---

# 12. Bank & Cash Account Module

Account Name, Bank Name, Account Number, IFSC, Branch, Account Type (bank/cash), Opening Balance, Status. Current balance is *derived* live from posted journal entries against the account's mapped ledger `Account`, not stored (the old `current_balance` column was dropped).

---

# 13. Masters

- **Units** — Piece, Bottle, Kg, Gram, Liter, Meter, Hour, Day, Month, Box, Packet, Other (admin-editable, not hardcoded).
- **Payment Methods** — Cash, Bank, UPI, Credit Card, Debit Card, Cheque, NEFT, RTGS, IMPS, Other.
- **GST Rate Master** (`gst_rates`) — Name, Rate, Status. `Product.gst_rate_id` points here (not a raw decimal on the product). A Purchase Bill / Sale Invoice line snapshots the rate *value* at creation time, so a later rate change never rewrites a historical document.
- **TDS Section Master** (`tds_sections`) — Section (e.g. "194C"), Description, Rate, Status. Used bidirectionally: a Purchase Bill deducts TDS paying a vendor; a Sale Invoice records TDS a customer deducted paying the business. Lives at the document level (`tds_section_id` + `tds_amount`), not per line.

Names on GST Rate / TDS Section are uniqueness-checked case-insensitively (`Rule::unique()`), since MySQL's `utf8mb4_unicode_ci` collation treats "194C" and "194c" as duplicates.

---

# 14. Inventory Management

## Products

SKU, Name, Description, Product Category, Unit, HSN Code, GST Rate (FK to GST Rate Master), Min/Max Stock Level, Status.

## Product Categories

Simple name/description/status master, parent-less.

## Stock Valuation — Weighted Average Cost (WAC)

Not FIFO, not a manually maintained standard cost. `StockLevelService::averageCostFor()` computes `(SUM(In total_cost) − SUM(Out total_cost)) / (SUM(In qty) − SUM(Out qty))` over all of a product's movements. `StockMovementService::postOut()` is the **sole** place that computes this — under `lockForUpdate()` so two concurrent sales of the same product can't read the same stale average — and snapshots the resulting `unit_cost`/`total_cost` onto the movement it creates, which every downstream COGS calculation (a Sale Invoice's accounting entry) then reads back rather than recomputing.

`postIn()` accepts an explicit `unit_cost` (used by Goods Receipt, sourced from the PO's locked price) or defaults to the current average for a Stock Adjustment increase.

## Stock Movements

Every stock change is a `stock_movements` row: Direction (In/Out), Quantity, Unit Cost, Total Cost, Movement Date, a polymorphic Source (which document caused it — `GoodsReceipt`, `DeliveryChallanItem`, `SaleInvoice`, or `StockAdjustment`), Reference Number, Notes.

## Stock Adjustments

Increase or Decrease, with a Reason (Opening Stock, Physical Count Correction, Damaged/Expired, Lost/Theft, Internal Use, Other). Requires approval (`stock-adjustments.approve`, blocked for the same user who created it — no self-approval). An Increase requires a `unit_cost` (defaults to the current average, overridable for a genuine Opening Stock entry) — a null-cost inflow would silently dilute every subsequent sale's COGS.

## Inventory Reports

Low Stock Report (products at/below `min_stock_level`), Stock Movements report (filterable by product/date range).

---

# 15. Procurement Module

## 15.1 Vendor Quotation

Quotation Number (auto, `QUO-<FY>-00001`), Vendor, Date, Line Items (Product, Quantity, Unit Price), Status: Draft → Submitted → Approved/Rejected → Expired/Converted. Approval is a separate step and the submitter cannot approve their own quotation.

## 15.2 Purchase Order

PO Number (auto, `PO-<FY>-00001`), created directly or from an Approved Quotation (`?from_quotation=`), Vendor, Expected Delivery Date, Line Items. Status: Draft → Sent → Partially Received / Received → Closed, or Cancelled at any point before Received. Status auto-recomputes from linked Goods Receipts.

## 15.3 Goods Receipt

GRN Number (auto, `GRN-<FY>-00001`), against a Sent/Partially Received PO. Draft is editable; `complete()` posts a stock-In movement per line at the PO's locked `unit_price` and is the point of no return — a completed GRN cannot be edited or re-completed. Guards against over-receiving by comparing the requested quantity to `SUM(already received across every completed GRN for that PO line)`, recomputed live inside the same transaction (never a stored running total).

## 15.4 Purchase Bill

Bill Number (auto, `BILL-<FY>-00001`), created from exactly one Completed, not-yet-billed Goods Receipt. Line price and quantity are locked to the GRN (no re-pricing — that would silently diverge the Bill's accounting entry from the stock's already-snapshotted cost). Status: Draft → Posted → Cancelled. `post()` writes the journal entry: **Dr Inventory, Dr Input GST, Cr Accounts Payable, Cr TDS Payable** (if a TDS section is set). `cancel()` is allowed from Draft, or from Posted only if no payments have been recorded against it.

---

# 16. Sales Module

## 16.1 Sale Order

Order Number (auto, `SO-<FY>-00001`), Customer, Order Date, Line Items (Product, Quantity, Unit Price). Status: Draft → Confirmed → Cancelled, or Converted once a Delivery Challan is raised against it. No approval gate.

## 16.2 Delivery Challan

Challan Number (auto, `DC-<FY>-00001`), direct or converted from a Confirmed Sale Order (`?sale_order_id=`), Customer, Date, Line Items (Product, Quantity, Notes — **no pricing**, it is a delivery note). Draft is editable; `complete()` posts a stock-Out movement per line (sourced to that specific `DeliveryChallanItem`, so a later Sale Invoice can look up its exact cost) and is the point of no return. Status: Draft → Completed → Partially Invoiced → Invoiced, or Cancelled while Draft. Printable PDF (no pricing/GST columns).

## 16.3 Sale Invoice

Invoice Number (auto, `INV-<FY>-00001`). Created either:

- **Direct** — no `delivery_challan_item_id` on any line; creation itself posts the stock-Out (this is the only document for the sale, so it must do both the delivery and the billing), or
- **From Challan(s)** — every line carries a `delivery_challan_item_id`; stock already left when the challan was completed, so the invoice does not move it again. Over-invoicing a challan line is rejected server-side by comparing the requested quantity against `SUM(already-invoiced quantity across every non-cancelled invoice for that line)`. The originating challan's status is recomputed after every invoice create/cancel.

Mixing direct and from-challan lines on one invoice is rejected outright.

Status: Draft → Posted → Cancelled. `post()` writes the journal entry: **Dr Accounts Receivable (net of TDS), Dr TDS Receivable (if applicable), Cr Sales, Cr GST Payable, Dr Cost of Goods Sold, Cr Inventory** (the COGS/Inventory pair sized from each line's weighted-average cost, snapshotted at the point stock actually moved — either at direct-invoice creation or at the originating challan's completion). `cancel()` is allowed from Draft, or from Posted only if no payments have been recorded. Printable GST-invoice-style PDF (HSN, CGST/SGST or IGST columns, TDS line, amount due).

---

# 17. GST Handling

- GST is captured per-line as three columns — `cgst_amount`, `sgst_amount`, `igst_amount` (at most two non-zero together) — with `gst_rate` and `hsn_code` snapshotted onto the line at creation time from the product's current GST Rate Master entry, never re-read live later.
- `GstCalculationService::splitGst()` compares `Company.state` against `Party.state` (both constrained to the same canonical `config('india.states')` list) — same state → CGST+SGST; different state → IGST. Shared by both Purchase Bill and Sale Invoice creation.
- The ledger keeps one combined **Input GST** account (purchase side) and one combined **GST Payable** account (sale side) in V1 — not segregated by CGST/SGST/IGST at the control-account level. The per-document/per-line breakdown is already fully captured and printable; only GSTR-filing-level segregation is deferred.
- `Company.state` and `Party.state` are both constrained dropdowns against the 37-entry India GST state/UT list (`config/india.php`), not free text — a mismatched string like "UP" vs "Uttar Pradesh" would otherwise silently pick the wrong CGST+SGST-vs-IGST branch.

---

# 18. TDS Handling

- TDS Section Master (`tds_sections`): Section, Description, Rate — configurable, not hardcoded, usable from either a Purchase Bill or a Sale Invoice (TDS is legally bidirectional: whoever pays deducts it).
- Lives at the document level: `tds_section_id` + computed `tds_amount`, not per line.
- `amountDue()` on both `PurchaseBill` and `SaleInvoice` is `total_amount − tds_amount − amountPaid()` — TDS is withheld/deducted by the paying side and never settled through a `Payment`, so it must be excluded from what's still collectible/payable in cash.
- Purchase side posts **Cr TDS Payable** (a liability — TDS the business owes to remit to the government). Sale side posts **Dr TDS Receivable** (an asset — TDS the customer already remitted on the business's behalf, claimable as a tax credit). Both control accounts exist in the Chart of Accounts and are idempotently backfilled for any company created before they were added.

---

# 19. Accounting Module

Full double-entry accounting via `AccountingService`. `postJournalEntry()` is the sole (private) choke point that ever writes a `JournalEntry` + its `JournalEntryItem` lines, and only checks that total debit equals total credit across the *whole* entry — not that sub-groups within it balance independently, which is what lets a Sale Invoice's revenue-recognition lines and its COGS lines post as one atomic entry.

## Chart of Accounts (system-seeded per company, idempotent)

- **Assets**: Cash & Bank (group), Accounts Receivable, Fixed Assets, Input GST, Inventory, TDS Receivable, Other Assets
- **Liabilities**: Accounts Payable, GST Payable, TDS Payable, Loans, Other Liabilities
- **Equity**: Capital, Retained Earnings, Drawings/Personal Use
- **Income**: Sales, Service Income, Other Income
- **Expenses**: Expenses (group, per-category sub-accounts auto-created), Cost of Goods Sold

## Journal

Every posting action (Expense approval, Purchase Bill post, Sale Invoice post, Payment) creates a `JournalEntry` with an auto-generated entry number and one or more `JournalEntryItem` debit/credit lines. Cancelling a posted document reverses its entry via a new, fully-balanced offsetting `JournalEntry` — history is never edited or deleted.

## Ledger, Trial Balance, Profit & Loss, Balance Sheet

All computed live from `journal_entries`/`journal_entry_items` for the selected financial year — never a stored snapshot.

---

# 20. Party Ledger

`PartyLedgerService`, entirely derived (nothing stored):

- **Outstanding Balances** (`/party-ledger`) — one batched, grouped query per source table (Sale Invoices, Purchase Bills, Payments In, Payments Out), combined per party into a single signed net figure: positive = the party owes the business ("You will get"), negative = the business owes the party ("You will give"). TDS is excluded from both sides, matching each document's own `amountDue()`.
- **Statement** (`/parties/{party}/ledger`) — every Posted Sale Invoice, Purchase Bill and Payment for that party, chronological, with a running balance computed on the fly.
- **Item-wise Transaction History** — every Sale Invoice / Purchase Bill line for that party, grouped by product, chronological (added per an amendment to the original scope — the ledger is product-level, not just a financial total).

Surfaced on the Dashboard as an "Outstanding Dues" widget (top parties by absolute balance).

---

# 21. Reports Module

Built (`ReportController`): Category-wise Expense, Vendor-wise Expense, Payment-method-wise Expense, Monthly Comparison (table/chart), Financial Year Summary, Daily Expense, Monthly Expense, Cash & Bank Book. All exportable to **Excel (.xlsx), CSV, and PDF** (`Maatwebsite\Excel` + `barryvdh/laravel-dompdf`).

Inventory-specific reports live under the Inventory module (Section 14): Low Stock, Stock Movements.

**Not built:** GST-specific reports (Input/Output GST register, GST summary), TDS-specific reports (TDS Payable/Deposited/Outstanding) as standalone report pages — the underlying data exists on every Bill/Invoice/Expense and is printable per-document, but there is no aggregate GST/TDS report view yet.

---

# 22. Dashboard

- Financial summary (today/this-month/this-FY expense totals), gated by an active financial year.
- Category/payment-method/top-vendor charts and a monthly trend chart (`reports.view`).
- Cash balance, bank balance, net profit, payable (`accounting.view`).
- Low Stock widget (`inventory.view`).
- Outstanding Dues widget (`parties.manage`).
- Company setup / financial-year setup prompts when either is missing.

---

# 23. Calendar / Daily Notes Module

`daily_notes` table: Date, Title, Content, Time, Category, per company and creator. A simple calendar/notes UI (`CalendarController`) for operational reminders, separate from the financial workflow. Permissions: `daily-notes.view` / `daily-notes.manage`.

---

# 24. Document Management

A single polymorphic `documents` table (`documentable_type`/`documentable_id`) rather than per-module attachment tables — any model can have documents attached. Fields: Name, Type, File Path, File Size, Uploaded By, Notes. Stored via Laravel's local disk; download is access-checked (`DocumentController::download`), not a public URL.

---

# 25. Audit Log

`AuditLogService` records: User, Action, Module/Entity Type, Record, Old Value, New Value, Date/Time — written from inside the relevant Service method (creation, status transitions, cancellations) across every financial/workflow module. Viewable at `/settings/audit-logs` (`audit-logs.view`).

---

# 26. Database Architecture (current)

## Company / Users

    companies, company_user (pivot), financial_years
    users, roles, permissions, role_has_permissions, model_has_roles, model_has_permissions (spatie)

## Masters

    expense_categories, expense_sub_categories, parties, vendor_products,
    payment_methods, units, gst_rates, tds_sections, bank_accounts,
    product_categories, products

## Expenses

    expenses (single table; no separate line-items — an expense is one record)

## Inventory

    stock_movements, stock_adjustments

## Procurement

    vendor_quotations, vendor_quotation_items, quotation_approvals,
    purchase_orders, purchase_order_items,
    goods_receipts, goods_receipt_items,
    purchase_bills, purchase_bill_items

## Sales

    sale_orders, sale_order_items,
    delivery_challans, delivery_challan_items,
    sale_invoices, sale_invoice_items

## Payments

    payments (polymorphic source_type/source_id — PurchaseBill or SaleInvoice)

## Accounting

    accounts, journal_entries, journal_entry_items

## Calendar

    daily_notes

## Documents / System

    documents, audit_logs, settings, cache, jobs

---

# 27. Laravel Architecture (current)

    Backend:            Laravel 13
    Database:            MySQL
    Frontend:            Blade + Tailwind CSS + Alpine.js (no Vue/React, no Bootstrap)
    Authentication:       Laravel Breeze
    Authorization:        spatie/laravel-permission
    File Storage:         Laravel Storage (local disk)
    PDF:                  barryvdh/laravel-dompdf
    Excel/CSV export:     maatwebsite/excel

    Controllers → Services → Models → Database

Controllers stay thin (validation via Form Requests, delegate to a Service, redirect). Business/accounting calculations live only in `app/Services/*.php`, never in Blade views or controllers directly. Multi-step operations that must succeed or fail together are wrapped in `DB::transaction()`.

## Actual folder organization

    app/
    +-- Models/
    +-- Http/
    |   +-- Controllers/
    |   |   +-- Accounting/     (ChartOfAccounts, Journal, Ledger, TrialBalance, ProfitLoss, BalanceSheet)
    |   |   +-- Calendar/
    |   |   +-- Company/        (Company, FinancialYear)
    |   |   +-- Expense/        (Expense, ExpenseCategory, ExpenseSubCategory)
    |   |   +-- Inventory/      (Product, ProductCategory, StockAdjustment, InventoryReport)
    |   |   +-- Masters/        (Unit, PaymentMethod, GstRate, TdsSection, BankAccount)
    |   |   +-- Party/          (Party, PartyLedger)
    |   |   +-- Procurement/    (VendorQuotation, QuotationApproval, PurchaseOrder, GoodsReceipt, PurchaseBill, PurchaseBillPayment)
    |   |   +-- Sales/          (SaleOrder, DeliveryChallan, SaleInvoice, SaleInvoicePayment)
    |   |   +-- Report/
    |   |   +-- Settings/       (User, AuditLog)
    |   |   +-- Vendor/         (VendorProduct — still vendor-only)
    |   |   +-- Concerns/       (EnsuresCompanyOwnership)
    |   +-- Requests/           (one FormRequest per writable resource)
    |
    +-- Services/
    |   +-- AccountingService.php, ExpenseService.php, PaymentService.php
    |   +-- StockMovementService.php, StockLevelService.php, StockAdjustmentService.php
    |   +-- VendorQuotationService.php, PurchaseOrderService.php, GoodsReceiptService.php, PurchaseBillService.php
    |   +-- SaleOrderService.php, DeliveryChallanService.php, SaleInvoiceService.php
    |   +-- GstCalculationService.php, PartyLedgerService.php, AuditLogService.php
    |   +-- ReportService.php, InventoryReportService.php, CompanyContextService.php
    |   +-- Concerns/GeneratesSequentialNumbers.php

---

# 28. Main Navigation (current sidebar)

    Dashboard

    Expenses
        All Expenses, Add Expense, Categories, Sub Categories

    Parties
        All Parties, Party Ledger

    Inventory
        Products, Product Categories, Stock Adjustments, Low Stock Report, Stock Movements

    Procurement
        Vendor Quotations, Compare Quotations, Purchase Orders, Goods Receipts, Purchase Bills

    Sales
        Sale Orders, Delivery Challans, Sale Invoices

    Reports
        All Reports, Category-wise, Vendor-wise, Payment-wise, Cash & Bank Book,
        Monthly Comparison, Financial Year Summary

    Masters
        Units, Payment Methods, GST Rates, TDS Sections, Bank & Cash Accounts

    Company
        Financial Years, Companies

    Accounting
        Chart of Accounts, Journal, Ledger, Trial Balance, Profit & Loss, Balance Sheet

    Administration
        Users, Audit Logs

Each group only renders for a user holding the relevant permission — an Accountant, for instance, never sees Administration.

---

# 29. Security Requirements

Implemented: password hashing (bcrypt via Laravel), CSRF protection on every form, session-based authentication, role/permission-based authorization on every route (`can:` middleware), server-side validation on every write (Form Requests), per-company data isolation (`EnsuresCompanyOwnership`), audit logging.

**Not built as an explicit standalone concern:** automated backup scheduling, documented backup-restore testing, SQL-injection-specific hardening beyond Eloquent's parameter binding (no raw-SQL concatenation is used, so this is covered by default rather than by a dedicated control).

---

# 30. Data Validation & Guards Actually Enforced

- Every quantity/amount field is validated numeric, `min:0` or `min:0.01` as appropriate, server-side (Form Requests), not just via an HTML `max=""` hint (an HTML-only guard was identified as insufficient during testing and always backed by a server-side check).
- Over-receiving a Purchase Order (Goods Receipt) and over-invoicing a Delivery Challan (Sale Invoice) are both guarded by summing already-consumed quantity across every non-cancelled downstream document, recomputed live inside the same transaction — never a stored running total that could go stale.
- GST Rate / TDS Section names are uniqueness-checked case-insensitively per company.
- A financial year that is locked blocks new stock movements and new postings.
- A document with payments recorded against it (`SUM(payments) > 0`) cannot be cancelled until those payments are reversed first.
- Self-approval is blocked for Vendor Quotation approval and Stock Adjustment approval.

---

# 31. Not Yet Built — Future Roadmap

Carried over from the original plan, still not implemented:

## Fixed Assets & Depreciation

Asset register, depreciation methods/schedules, accumulated depreciation, book value, disposal tracking.

## CA / ITR Module

CA annual-summary dashboard, structured CA export package (Sales/Expenses/Purchases/GST/TDS/Ledger/Trial Balance/P&L/Balance Sheet/Fixed Assets/ITR Summary as a folder of Excel files), ITR income/deduction data preparation.

## GST/TDS Aggregate Reports

Standalone Input/Output GST register and summary reports, TDS Payable/Deposited/Outstanding reports (the underlying per-document data already exists — this is an aggregation/reporting layer on top of it).

## Automation

OCR bill scanning, bank statement import + reconciliation, automatic vendor/category detection, duplicate invoice detection, recurring expenses, payment/tax reminders, missing-document alerts.

## Mobile

A mobile-optimized quick-expense-entry flow.

## Deliberate V1 Limitations (documented, not oversights)

- A `Payment` settles exactly one Purchase Bill or Sale Invoice — no on-account/advance payment allocated across multiple documents later.
- GST ledger accounts are combined (one Input GST, one GST Payable) rather than segregated by CGST/SGST/IGST at the control-account level.
- A Purchase Bill's line price/quantity is locked to its originating Goods Receipt — no re-pricing after receipt (a landed-cost/price-variance feature, deferred).
- No multi-tier approval `level` column is in active use anywhere it exists in the schema — reserved for a future workflow, not wired up.

---

# 32. Development Order (actual, chronological)

    1. Foundation: Auth, Company, Financial Year, Roles/Permissions, Dashboard shell
    2. Expense Management: Categories, Sub-categories, Vendors, Expense entry, Documents
    3. Reports: category/vendor/payment/monthly/FY reports, Excel/CSV/PDF export
    4. Accounting: Chart of Accounts, Journal, Ledger, Trial Balance, P&L, Balance Sheet
    5. Multi-company support, Dark mode, Calendar/Daily Notes
    6. Inventory Management: Products, Categories, Stock Adjustments, WAC costing engine
    7. Party unification (Vendor → Party, is_vendor/is_customer)
    8. GST Rate Master, TDS Section Master (replacing free-text fields)
    9. Procurement: Vendor Quotation → Approval → Purchase Order → Goods Receipt → Purchase Bill → Payment
    10. Sales: Sale Order → Delivery Challan → Sale Invoice → Payment
    11. Party Ledger (outstanding balances, statement, item-wise history) + dashboard tie-in

    Remaining (see Section 31): GST/TDS aggregate reports, Fixed Assets, CA/ITR export, Automation, Mobile.

---

# 33. Important Rules Enforced (keep following these)

1. Never hardcode expense categories, product categories, units, GST rates, or TDS sections — all admin-managed masters.
2. Never hardcode financial years — everything scopes through `financial_years`.
3. Never permanently delete a financial or workflow record — status transitions and reversing entries only.
4. Keep accounting/costing logic out of Blade views and out of controllers — Services only.
5. Every new permission name must be added to `RolePermissionSeeder` **and** the seeder re-run, or it silently 403s for everyone.
6. Every stock-out valuation goes through `StockMovementService::postOut()` — never compute or pass a cost from a caller.
7. Route ordering: literal segments (`create`, `compare`) must be registered before a `{model}` wildcard of the same HTTP method.
8. Every new financial/workflow table is scoped by `company_id`, checked via `EnsuresCompanyOwnership` in its controller.
9. Design reports and ledgers from source transaction data, never a manually-maintained running total.
10. Keep documents polymorphically linked to whatever they're attached to, not a per-module table.
11. Tax (GST/TDS) figures are snapshotted onto a document line at creation time — never live-recomputed from a master that may have changed since.
12. Any accounting-posting change must be re-verified against the trial balance after posting AND after cancelling, not just checked for "the call didn't throw."

---

# 34. Final Goal

The system has already moved from a simple expense tracker to:

    Expense Management + Multi-Company + Inventory + Procurement + Sales/Billing
          +
    Full Double-Entry Accounting (GST + TDS aware)
          +
    Derived Party Ledger

The remaining stretch toward the original vision is:

          +
    Fixed Assets & Depreciation
          +
    GST/TDS Aggregate Reporting
          +
    CA / ITR Preparation Data
          +
    Automation (OCR, bank import, recurring entries)

The core principle remains unchanged:

    ENTER DATA ONCE

and let the system derive everything else from it — stock levels, costs, account balances, party balances, and every report — rather than storing the same figure twice.
