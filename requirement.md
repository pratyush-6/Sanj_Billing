# Sanjeevani Expense, Accounting & Tax Management System
## Implementation Plan

---

# 1. Project Overview

## Project Name

Sanjeevani Expense, Accounting & Tax Management System

## Purpose

The primary purpose of this system is to provide Sanjeevani with a centralized platform to:

- Record all business expenses.
- Categorize expenses.
- Maintain vendor/supplier information.
- Track cash and bank payments.
- Store bills and supporting documents.
- Track GST and TDS information.
- Generate monthly and yearly expense reports.
- Maintain accounting records.
- Generate Profit & Loss and Balance Sheet reports.
- Maintain fixed assets and depreciation.
- Generate CA-ready financial and tax reports.
- Prepare structured data required for ITR filing.
- Support future expansion without requiring major database changes.

The system should be designed as a scalable accounting platform rather than a simple expense-entry application.

---

# 2. Main Business Objective

The system should answer the following questions at any time:

1. How much did Sanjeevani spend?
2. Where was the money spent?
3. Which category did the expense belong to?
4. Which vendor/supplier received the payment?
5. How was the payment made?
6. Which bank/cash account was used?
7. Is the expense business-related?
8. Is GST applicable?
9. Is TDS applicable?
10. Is supporting documentation available?
11. How much was spent in a particular month?
12. How much was spent in a financial year?
13. Which expense categories are increasing?
14. What is the total business expense?
15. What is the business profit?
16. What data needs to be provided to the CA?
17. What information is required for tax/ITR preparation?

---

# 3. Core Workflow

The basic system workflow will be:

    Expense Happens
          |
          v
    Enter Expense
          |
          v
    Select Category
          |
          v
    Select Vendor
          |
          v
    Enter Amount / Quantity
          |
          v
    Add GST / TDS if applicable
          |
          v
    Select Payment Method
          |
          v
    Select Bank / Cash Account
          |
          v
    Attach Bill / Receipt
          |
          v
    Save Expense
          |
          v
    Accounting Entry
          |
          v
    Reports
          |
          v
    Financial Year Summary
          |
          v
    CA / Tax / ITR Data

---

# 4. Design Principles

The system must follow these principles:

## 4.1 Dynamic Categories

Expense categories must not be hardcoded.

Examples:

- Mineral Water
- Mill
- Electricity
- Transport
- Rent
- Salary
- Internet
- Packaging
- Maintenance
- Professional Fees

New categories must be creatable from the admin panel.

No code/database modification should be required to add a new expense category.

---

## 4.2 Single Entry Principle

An expense should be entered only once.

The same expense record should automatically contribute to:

- Expense reports
- Vendor reports
- Cash book
- Bank book
- GST reports
- TDS reports
- Ledger
- Profit & Loss
- Tax summary
- CA reports

---

## 4.3 Financial Year Based System

The system must support financial years.

Example:

    FY 2026-27
    01-Apr-2026 to 31-Mar-2027

Financial year must not be hardcoded.

---

## 4.4 Auditability

Financial records should not be permanently deleted.

Use:

- Draft
- Approved
- Cancelled
- Reversed

and maintain audit logs.

---

# 5. Application Modules

The system will contain the following modules:

1. Authentication
2. Company Management
3. Financial Year
4. Dashboard
5. Expense Categories
6. Vendors
7. Expenses
8. Payments
9. Bank Accounts
10. Cash Management
11. Documents
12. GST
13. TDS
14. Fixed Assets
15. Accounting
16. Reports
17. CA / Tax
18. ITR Data
19. User & Permissions
20. Audit Logs
21. Settings

---

# 6. Authentication Module

## Features

- Login
- Logout
- Forgot Password
- Reset Password
- Change Password
- User Profile
- Session Management

## Roles

Initial roles:

- Super Admin
- Admin
- Accountant
- CA
- Viewer

---

# 7. Company Module

The system should support company/business information.

## Company Fields

- Company Name
- Legal Name
- Business Type
- PAN
- TAN
- GSTIN
- CIN
- Address
- State
- City
- Pincode
- Email
- Phone
- Website
- Financial Year
- Business Description

## Business Types

Examples:

- Proprietorship
- Partnership
- LLP
- Private Limited
- Public Limited
- Individual
- Other

The system should allow future support for multiple companies/businesses.

---

# 8. Financial Year Module

## Features

- Create Financial Year
- Set Active Financial Year
- Lock Financial Year
- Close Financial Year
- View Previous Financial Years

Example:

    FY 2025-26
    FY 2026-27
    FY 2027-28

Transactions must belong to a financial year.

---

# 9. Expense Category Module

This is one of the most important modules.

## Category Fields

- Category Name
- Category Code
- Description
- Expense Nature
- Parent Category
- Tax Applicability
- Status

## Example Categories

### Direct Expenses

- Raw Material
- Mill Charges
- Production Charges
- Packaging

### Operating Expenses

- Mineral Water
- Transport
- Fuel
- Electricity
- Internet
- Telephone
- Maintenance

### Administrative Expenses

- Office Rent
- Office Supplies
- Professional Fees
- Software
- Stationery

### Employee Expenses

- Salary
- Bonus
- Travel
- Employee Welfare

### Financial Expenses

- Bank Charges
- Interest
- Loan Charges

### Capital Expenses

- Computer
- Machinery
- Furniture
- Vehicle

---

# 10. Sub Category Module

Categories can have subcategories.

Example:

    Transport
        |
        +-- Local Transport
        +-- Truck
        +-- Taxi
        +-- Fuel

    Mineral Water
        |
        +-- Drinking Water
        +-- Bottled Water

    Maintenance
        |
        +-- Machine Maintenance
        +-- Building Maintenance
        +-- Vehicle Maintenance

This provides detailed reporting.

---

# 11. Vendor / Supplier Module

## Vendor Fields

- Vendor ID
- Vendor Name
- Company Name
- Contact Person
- Mobile
- Email
- Address
- State
- City
- Pincode
- GSTIN
- PAN
- Bank Name
- Account Number
- IFSC
- Payment Terms
- Status

## Vendor Features

- Add Vendor
- Edit Vendor
- View Vendor
- Vendor Ledger
- Vendor Expense History
- Vendor Outstanding
- Vendor Documents

---

# 12. Expense Module

This is the core module.

## Expense Fields

- Expense Number
- Expense Date
- Financial Year
- Category
- Sub Category
- Vendor
- Description
- Quantity
- Unit
- Rate
- Taxable Amount
- Discount
- GST
- TDS
- Total Amount
- Payment Mode
- Payment Account
- Invoice Number
- Invoice Date
- Business/Personal
- Expense Nature
- Notes
- Status

---

# 13. Expense Nature

Every expense should be classified.

Options:

- Direct Expense
- Indirect Expense
- Operating Expense
- Administrative Expense
- Financial Expense
- Capital Expense
- Personal / Non-business
- Other

---

# 14. Business / Personal Classification

Every expense should have:

- Business
- Personal
- Mixed

For Mixed:

    Total Amount = ₹10,000
    Business Portion = ₹7,000
    Personal Portion = ₹3,000

Only the business portion should be included in business accounting/tax reports where appropriate.

---

# 15. Quantity-Based Expenses

The system must support:

- Quantity
- Unit
- Rate
- Total

Example:

    Mineral Water

    Quantity = 20
    Unit = Bottle
    Rate = ₹50

    Amount = ₹1,000

Units should be configurable:

- Piece
- Bottle
- Kg
- Gram
- Liter
- Meter
- Hour
- Day
- Month
- Box
- Packet
- Other

---

# 16. Fixed Amount Expenses

The system should also support expenses without quantity.

Example:

    Office Rent
    Amount = ₹25,000

    Electricity
    Amount = ₹12,500

Quantity and rate should be optional.

---

# 17. Payment Module

## Payment Methods

- Cash
- Bank
- UPI
- Credit Card
- Debit Card
- Cheque
- NEFT
- RTGS
- IMPS
- Other

Each payment should contain:

- Payment Date
- Amount
- Payment Mode
- Account
- Reference Number
- Notes

---

# 18. Bank Account Module

The system should support multiple accounts.

Examples:

- HDFC Current Account
- HDFC Savings Account
- SBI Account
- ICICI Account
- Cash
- Petty Cash
- Credit Card
- UPI

## Bank Account Fields

- Account Name
- Bank Name
- Account Number
- IFSC
- Branch
- Account Type
- Opening Balance
- Current Balance
- Status

---

# 19. Cash Management

Maintain:

- Cash Opening Balance
- Cash Receipts
- Cash Expenses
- Cash Transfers
- Cash Closing Balance

Reports:

- Daily Cash Book
- Monthly Cash Book
- Financial Year Cash Book

---

# 20. Document Management

Every expense should support document attachments.

## Supported Documents

- Invoice
- Bill
- Receipt
- Payment Proof
- Bank Statement
- GST Document
- TDS Document
- Other

## Document Fields

- Document Name
- Document Type
- File Path
- File Size
- Uploaded By
- Upload Date
- Related Expense
- Related Vendor
- Notes

---

# 21. Expense Status

Possible statuses:

- Draft
- Pending Approval
- Approved
- Rejected
- Cancelled

For initial single-user implementation:

    Draft
    Approved
    Cancelled

can be sufficient.

---

# 22. Recurring Expenses

Future feature.

Examples:

- Rent
- Internet
- Software Subscription
- Salary
- Insurance
- Maintenance

Configuration:

- Expense Category
- Vendor
- Amount
- Frequency
- Start Date
- End Date
- Payment Method

Frequencies:

- Monthly
- Quarterly
- Half-Yearly
- Yearly

---

# 23. GST Module

GST information must be optional per transaction.

## GST Fields

- GST Applicable
- GSTIN
- Invoice Number
- Invoice Date
- Taxable Amount
- GST Rate
- CGST
- SGST
- IGST
- Cess
- Total GST

## GST Types

- CGST
- SGST
- IGST
- Cess

---

# 24. GST Reports

Reports should include:

- Input GST
- Output GST
- Vendor-wise GST
- Monthly GST
- Financial Year GST
- GST Invoice Register
- GST Summary

The system should preserve transaction-level data so the CA can review it.

---

# 25. TDS Module

TDS should be optional per transaction.

## Fields

- TDS Applicable
- TDS Section
- TDS Rate
- Gross Amount
- TDS Amount
- Net Payment
- Deduction Date
- Deposit Date
- Challan Number
- Status

Tax rates/sections should be configurable rather than hardcoded.

---

# 26. TDS Reports

- TDS Deducted
- Vendor-wise TDS
- Monthly TDS
- Financial Year TDS
- TDS Payable
- TDS Deposited
- TDS Outstanding

---

# 27. Fixed Asset Module

Capital purchases should be separated from normal expenses.

## Examples

- Computer
- Machinery
- Furniture
- Vehicle
- Equipment

## Asset Fields

- Asset Name
- Asset Code
- Category
- Purchase Date
- Purchase Value
- Vendor
- Invoice Number
- GST
- Useful Life
- Depreciation Method
- Depreciation Rate
- Accumulated Depreciation
- Current Book Value
- Disposal Date
- Status

---

# 28. Accounting Module

The accounting module should be based on double-entry accounting.

## Chart of Accounts

### Assets

- Cash
- Bank
- Accounts Receivable
- Fixed Assets
- Other Assets

### Liabilities

- Accounts Payable
- GST Payable
- TDS Payable
- Loans
- Other Liabilities

### Income

- Sales
- Service Income
- Other Income

### Expenses

- Mineral Water
- Mill Charges
- Transport
- Electricity
- Rent
- Salary
- Internet
- Professional Fees
- Bank Charges
- Other Expenses

---

# 29. Journal Entry

Every accounting transaction should generate appropriate journal entries.

Example:

    Office Expense A/c      Dr ₹10,000
            To Bank A/c            ₹10,000

Example with GST:

    Expense A/c             Dr ₹10,000
    Input GST A/c           Dr ₹1,800
            To Bank/Vendor A/c    ₹11,800

The exact accounting treatment should be configurable/reviewable by the CA.

---

# 30. Ledger

The system should maintain ledgers for:

- Vendors
- Customers if billing is added later
- Bank Accounts
- Cash
- Expenses
- GST
- TDS
- Assets
- Liabilities
- Income

Ledger should support:

- Date
- Particular
- Debit
- Credit
- Balance
- Reference

---

# 31. Trial Balance

Generate:

    Account
    Debit
    Credit

Example:

    Cash                 ₹50,000
    Bank               ₹2,00,000
    Expenses             ₹80,000
    GST Input             ₹10,000
    Capital                         ₹1,50,000
    Payables                        ₹90,000

Total Debit must equal Total Credit.

---

# 32. Profit & Loss

Generate:

## Income

- Business Revenue
- Other Income

## Expenses

- Direct Expenses
- Operating Expenses
- Administrative Expenses
- Employee Expenses
- Financial Expenses
- Depreciation
- Other Expenses

Then:

    Gross Profit
    Operating Profit
    Profit Before Tax

---

# 33. Balance Sheet

Generate:

## Assets

- Cash
- Bank
- Receivables
- Fixed Assets
- Other Assets

## Liabilities

- Payables
- GST Payable
- TDS Payable
- Loans
- Other Liabilities

## Equity

- Capital
- Retained Earnings
- Current Year Profit

---

# 34. Dashboard

Dashboard should display:

## Financial Summary

- Total Expense
- Business Expense
- Personal Expense
- Total Income
- Net Profit
- Cash Balance
- Bank Balance
- Receivable
- Payable

## Tax Summary

- Input GST
- Output GST
- GST Payable
- TDS Deducted
- TDS Payable

## Expense Summary

- Today's Expense
- This Month
- Current Financial Year

---

# 35. Expense Reports

Required reports:

## Daily Expense

Filter:

- Date

## Monthly Expense

Filter:

- Month
- Financial Year

## Category-wise Expense

Example:

    Mineral Water       ₹25,000
    Mill Charges        ₹85,000
    Transport           ₹42,000

## Vendor-wise Expense

Example:

    ABC Water Supplier  ₹25,000
    XYZ Mill            ₹85,000

## Payment-wise Expense

Example:

    Cash                ₹50,000
    HDFC Bank          ₹2,50,000
    UPI                 ₹25,000

---

# 36. Monthly Comparison Report

Show expenses across months.

Example:

    Category        Apr    May    Jun    Jul    Aug

    Mineral Water   20K    22K    25K    23K    25K
    Mill            70K    75K    80K    82K    85K
    Transport       35K    38K    40K    39K    42K

This report should support:

- Table
- Bar Chart
- Line Chart

---

# 37. Financial Year Expense Report

Example:

    FY 2026-27

    Mineral Water       ₹2,85,000
    Mill Charges       ₹10,25,000
    Transport            ₹5,40,000
    Electricity          ₹3,25,000
    Rent                 ₹3,00,000
    Salary              ₹18,50,000
    Other                ₹4,25,000

    Total Expenses      ₹47,50,000

---

# 38. Search and Filters

Expense listing must support:

- Date From
- Date To
- Category
- Sub Category
- Vendor
- Amount
- Payment Mode
- Bank Account
- GST
- TDS
- Business/Personal
- Expense Nature
- Status
- Bill Available
- Financial Year

---

# 39. Export System

Reports should be exportable to:

- Excel
- CSV
- PDF

Required exports:

- Expense Report
- Vendor Report
- GST Report
- TDS Report
- Ledger
- Trial Balance
- P&L
- Balance Sheet
- Fixed Asset Report
- CA Report
- ITR Summary

---

# 40. CA Dashboard

The CA section should provide an annual overview.

Example:

    Financial Year: 2026-27

    Revenue                     ₹XX
    Business Expenses           ₹XX
    Depreciation                ₹XX
    Profit Before Tax           ₹XX

    Input GST                   ₹XX
    Output GST                  ₹XX

    TDS Deducted                ₹XX

    Cash                        ₹XX
    Bank                        ₹XX
    Receivables                 ₹XX
    Payables                    ₹XX

---

# 41. CA Export Package

Generate a structured export package:

    CA_2026_27/
    |
    +-- Sales.xlsx
    +-- Expenses.xlsx
    +-- Purchases.xlsx
    +-- GST.xlsx
    +-- TDS.xlsx
    +-- Bank_Transactions.xlsx
    +-- Cash_Book.xlsx
    +-- Vendor_Ledger.xlsx
    +-- General_Ledger.xlsx
    +-- Trial_Balance.xlsx
    +-- Profit_Loss.xlsx
    +-- Balance_Sheet.xlsx
    +-- Fixed_Assets.xlsx
    +-- ITR_Summary.xlsx
    +-- Supporting_Documents/

---

# 42. ITR Data Preparation

The system should prepare structured information for the CA/ITR process.

## Income

Potential categories:

- Business Income
- Salary
- Interest
- Rental Income
- Capital Gains
- Other Income

## Deductions

Provide configurable fields for applicable deductions.

Examples may include:

- Section 80C
- Section 80D
- Section 80G
- Section 80TTA
- Section 80TTB
- Other applicable deductions

The actual applicability must be reviewed against the taxpayer's situation and current tax rules.

---

# 43. Assets for Tax Reporting

Maintain:

- Property
- Vehicles
- Machinery
- Equipment
- Bank Accounts
- Investments
- Shares
- Mutual Funds
- Fixed Deposits
- Other Assets

---

# 44. Liabilities for Tax Reporting

Maintain:

- Business Loans
- Home Loans
- Personal Loans
- Credit Cards
- Vendor Payables
- Other Liabilities

---

# 45. ITR Design Principle

The software should initially focus on:

    Accurate Data Collection
            |
            v
    Accounting
            |
            v
    Financial Statements
            |
            v
    Tax Data Preparation
            |
            v
    CA Review
            |
            v
    ITR Filing

The system should not blindly determine the final ITR form or tax treatment without CA review.

Tax rules and ITR requirements can change, so tax-related configurations must be maintainable.

---

# 46. Audit Log

Every important financial action should be recorded.

## Log Fields

- User
- Action
- Module
- Record ID
- Old Value
- New Value
- Date/Time
- IP Address
- User Agent

## Examples

    Expense Created
    Expense Updated
    Expense Cancelled
    Payment Added
    GST Updated
    Vendor Updated
    Account Modified

---

# 47. Soft Delete / Cancellation

Financial records should not normally be physically deleted.

Instead:

    Active
    Cancelled
    Reversed

The system must retain the original transaction and audit history.

---

# 48. Database Architecture

Initial database structure:

## Company

    companies
    financial_years

## Users

    users
    roles
    permissions
    role_permissions

## Masters

    expense_categories
    expense_sub_categories
    vendors
    payment_methods
    units
    tax_rates

## Expenses

    expenses
    expense_items
    expense_payments
    expense_documents

## Banking

    bank_accounts
    bank_transactions
    cash_transactions

## Tax

    gst_transactions
    tds_transactions

## Accounting

    accounts
    journal_entries
    journal_entry_items
    ledger_entries

## Assets

    assets
    asset_depreciations

## Documents

    documents

## System

    audit_logs
    notifications
    settings

---

# 49. Expense Database Concept

The expense should be designed approximately as:

    expenses
    |
    +-- company_id
    +-- financial_year_id
    +-- category_id
    +-- sub_category_id
    +-- vendor_id
    +-- expense_date
    +-- expense_number
    +-- description
    +-- quantity
    +-- unit_id
    +-- rate
    +-- taxable_amount
    +-- discount
    +-- gst_amount
    +-- tds_amount
    +-- total_amount
    +-- business_amount
    +-- personal_amount
    +-- payment_mode
    +-- payment_account
    +-- invoice_number
    +-- invoice_date
    +-- expense_nature
    +-- status
    +-- notes
    +-- created_by

---

# 50. Important Database Rules

## Rule 1

Do not create tables such as:

    mineral_water_expenses
    mill_expenses
    electricity_expenses

Instead:

    expense_categories
    expenses

---

## Rule 2

Do not hardcode GST rates.

Use configurable tax rates.

---

## Rule 3

Do not hardcode financial years.

Use financial_years table.

---

## Rule 4

Do not permanently delete accounting transactions.

Use cancellation/reversal.

---

## Rule 5

Every financial transaction should have an audit trail.

---

# 51. API Architecture

If the application requires API support, use REST APIs.

Example:

    /api/auth/login

    /api/companies

    /api/financial-years

    /api/expense-categories

    /api/vendors

    /api/expenses

    /api/payments

    /api/bank-accounts

    /api/gst

    /api/tds

    /api/reports

    /api/ca

    /api/itr

---

# 52. Laravel Architecture

Recommended stack:

    Backend:
    Laravel

    Database:
    MySQL

    Frontend:
    Blade + Bootstrap/modern UI

    Authentication:
    Laravel authentication

    File Storage:
    Laravel Storage

    PDF:
    Laravel-compatible PDF library

    Excel:
    Laravel Excel

The architecture should use:

    Controllers
        |
    Services
        |
    Repositories (where useful)
        |
    Models
        |
    Database

Business/accounting calculations should not be placed directly inside Blade views.

---

# 53. Recommended Laravel Modules

Suggested code organization:

    app/
    |
    +-- Models/
    |
    +-- Http/
    |   +-- Controllers/
    |   |   +-- Expense/
    |   |   +-- Vendor/
    |   |   +-- Accounting/
    |   |   +-- Tax/
    |   |   +-- Report/
    |   |   +-- CA/
    |
    +-- Services/
    |   +-- ExpenseService.php
    |   +-- PaymentService.php
    |   +-- AccountingService.php
    |   +-- GstService.php
    |   +-- TdsService.php
    |   +-- ReportService.php
    |   +-- TaxService.php
    |
    +-- Jobs/
    |
    +-- Notifications/
    |
    +-- Policies/

---

# 54. Phase 1 - Foundation

## Features

- Project setup
- Authentication
- User management
- Company profile
- Financial year
- Settings
- Roles and permissions
- Database structure

## Deliverables

- Working Laravel application
- Login
- Company setup
- Financial year setup
- Admin dashboard

---

# 55. Phase 2 - Expense Management

## Features

- Expense category
- Subcategory
- Vendor
- Expense entry
- Quantity-based expense
- Fixed expense
- Business/personal classification
- Payment mode
- Bank/cash account
- Expense attachment
- Expense search
- Expense edit
- Expense cancellation

## Deliverable

Complete expense management system.

---

# 56. Phase 3 - Reports

## Features

- Daily report
- Monthly report
- Category report
- Vendor report
- Payment report
- Bank report
- Cash report
- Financial year report
- Monthly comparison
- Dashboard charts
- Excel export
- PDF export

---

# 57. Phase 4 - Accounting

## Features

- Chart of Accounts
- Journal
- Double-entry accounting
- Ledger
- Cash book
- Bank book
- Trial Balance
- Profit & Loss
- Balance Sheet

---

# 58. Phase 5 - GST and TDS

## Features

- GST configuration
- Input GST
- Output GST
- GST transaction register
- GST reports
- TDS configuration
- TDS transactions
- TDS reports

---

# 59. Phase 6 - Fixed Assets

## Features

- Asset management
- Asset categories
- Purchase tracking
- Depreciation
- Asset disposal
- Asset register

---

# 60. Phase 7 - CA / ITR

## Features

- Annual tax summary
- Income summary
- Expense summary
- Asset summary
- Liability summary
- GST summary
- TDS summary
- CA dashboard
- CA export package
- ITR preparation data

---

# 61. Phase 8 - Automation

Future features:

- OCR bill scanning
- Automatic vendor detection
- Automatic category suggestion
- Bank statement import
- CSV import
- Excel import
- Duplicate invoice detection
- Recurring expenses
- Payment reminders
- Tax reminders
- Missing document alerts

---

# 62. Future Mobile Support

The system should eventually support a mobile-friendly expense entry screen.

Example:

    Open App
        |
        v
    + Add Expense
        |
        v
    Take Bill Photo
        |
        v
    Enter Amount
        |
        v
    Select Category
        |
        v
    Save

This should be optimized for quick expense entry.

---

# 63. Future OCR Workflow

Example:

    Upload Bill
        |
        v
    OCR Processing
        |
        v
    Extract:
        Vendor
        Invoice No
        Date
        GSTIN
        Amount
        GST
        Total
        |
        v
    User Confirmation
        |
        v
    Create Expense

OCR should assist the user rather than automatically creating an accounting entry without confirmation.

---

# 64. Future Bank Import

Support:

    CSV
    Excel
    Bank Statement

Example:

    Date | Description | Debit | Credit | Balance

System should allow:

- Import
- Duplicate detection
- Transaction matching
- Category suggestion
- Bank reconciliation

---

# 65. Security Requirements

The system should implement:

- Password hashing
- CSRF protection
- Authentication
- Authorization
- Role-based permissions
- Input validation
- SQL injection protection
- File upload validation
- Secure document access
- Audit logs
- Session security
- Regular database backups

---

# 66. Backup Strategy

Financial data is critical.

Implement:

## Database Backup

- Daily automatic backup
- Weekly backup
- Monthly archive

## Documents

Backup:

- Bills
- Invoices
- Receipts
- Bank statements
- Tax documents

Backup restoration should be tested periodically.

---

# 67. Data Validation

Before saving an expense:

Required:

- Expense Date
- Category
- Amount
- Payment Mode
- Financial Year

Optional:

- Vendor
- GST
- TDS
- Bill Number
- Attachment

Validation should prevent:

- Negative amounts unless explicitly supported
- Invalid dates
- Invalid GSTIN format
- Invalid tax calculations
- Invalid financial year
- Duplicate invoice numbers where applicable

---

# 68. Duplicate Detection

The system should warn if the same vendor has:

- Same invoice number
- Same invoice date
- Same amount

Example:

    Warning:
    Similar expense already exists.

    Vendor: ABC Water
    Invoice: MW-458
    Amount: ₹1,180

User can then confirm whether it is a duplicate.

---

# 69. Dashboard Charts

Recommended charts:

## Expense by Category

Pie/Donut chart.

## Monthly Expenses

Line chart.

## Category Comparison

Bar chart.

## Payment Method

Donut chart.

## Top Vendors

Bar chart.

## Monthly Expense Trend

Line chart.

---

# 70. Main Navigation

Recommended navigation:

    Dashboard

    Expenses
        All Expenses
        Add Expense
        Categories
        Sub Categories

    Vendors
        All Vendors
        Add Vendor
        Vendor Ledger

    Payments
        Payments
        Cash
        Bank

    Documents

    Tax
        GST
        TDS

    Assets

    Accounting
        Chart of Accounts
        Journal
        Ledger
        Trial Balance
        Profit & Loss
        Balance Sheet

    Reports
        Expense Reports
        Vendor Reports
        GST Reports
        TDS Reports
        Financial Reports

    CA / ITR
        Annual Summary
        Tax Data
        CA Export

    Settings
        Company
        Financial Year
        Users
        Roles
        Tax Settings
        General Settings

---

# 71. V1 Scope

The first production version should include:

- Authentication
- Company
- Financial Year
- Expense Categories
- Sub Categories
- Vendors
- Expenses
- Payment Methods
- Bank/Cash Accounts
- Documents
- Expense Dashboard
- Daily Report
- Monthly Report
- Category Report
- Vendor Report
- Financial Year Report
- Excel Export
- PDF Export

This is the recommended MVP.

---

# 72. V2 Scope

After V1 is stable:

- Accounting
- Ledger
- Trial Balance
- P&L
- Balance Sheet
- GST
- TDS
- Fixed Assets
- Depreciation

---

# 73. V3 Scope

CA and tax features:

- CA Dashboard
- Annual Summary
- ITR Data
- Tax Summary
- CA Export
- Supporting Documents
- Tax-related review workflow

---

# 74. V4 Scope

Automation:

- OCR
- Bank import
- Auto categorization
- Duplicate detection
- Recurring expenses
- Notifications
- Mobile experience
- CA login

---

# 75. Recommended Development Order

The implementation should follow this order:

    1. Database design
           |
    2. Authentication
           |
    3. Company
           |
    4. Financial Year
           |
    5. Expense Categories
           |
    6. Vendors
           |
    7. Bank/Cash Accounts
           |
    8. Expense Entry
           |
    9. Document Upload
           |
    10. Expense Dashboard
           |
    11. Reports
           |
    12. Export
           |
    13. Accounting
           |
    14. GST
           |
    15. TDS
           |
    16. Fixed Assets
           |
    17. CA Reports
           |
    18. ITR Data
           |
    19. Automation

---

# 76. Final System Architecture

The complete system should eventually follow:

                    SANJEEVANI
                        |
        +---------------+---------------+
        |               |               |
     Expenses        Income          Assets
        |               |               |
     Vendors         Sales           Fixed Assets
        |               |               |
     Payments       Receipts       Depreciation
        |               |               |
        +---------------+---------------+
                        |
                   ACCOUNTING
                        |
             +----------+----------+
             |          |          |
           Ledger      GST        TDS
             |          |          |
             +----------+----------+
                        |
                  FINANCIAL REPORTS
                        |
        +---------------+---------------+
        |               |               |
       P&L        Balance Sheet    Trial Balance
                        |
                   TAX SUMMARY
                        |
                    CA REVIEW
                        |
                   ITR DATA
                        |
                  ITR FILING

---

# 77. Success Criteria

The system will be considered successful when Sanjeevani can:

1. Add a new expense category without developer involvement.
2. Add any new vendor.
3. Record any type of expense.
4. Upload the supporting bill.
5. Record how the expense was paid.
6. Track cash and bank balances.
7. Search historical expenses.
8. Generate monthly expense reports.
9. Generate category-wise reports.
10. Generate vendor-wise reports.
11. Generate financial-year reports.
12. Track GST.
13. Track TDS.
14. Track fixed assets.
15. Generate accounting reports.
16. Generate Profit & Loss.
17. Generate Balance Sheet.
18. Generate Trial Balance.
19. Generate CA-ready reports.
20. Generate structured ITR preparation data.
21. Maintain complete audit history.
22. Add new expense types/categories without changing the application architecture.

---

# 78. Important Future-Proofing Rules

The following must be followed during development:

1. Never hardcode expense categories.
2. Never hardcode financial years.
3. Never hardcode tax rates.
4. Never permanently delete financial transactions.
5. Keep accounting logic separate from UI.
6. Keep tax configuration editable.
7. Keep documents linked to transactions.
8. Maintain audit logs.
9. Use database transactions for accounting operations.
10. Keep all financial calculations centralized in services.
11. Do not duplicate the same financial information in multiple tables unnecessarily.
12. Design reports from transaction data instead of manually storing report totals.
13. Keep the system multi-company capable even if Sanjeevani is the only company initially.
14. Keep the system extensible for future billing/sales functionality.
15. Tax and ITR decisions must remain reviewable by the CA.

---

# 79. Final Goal

The final Sanjeevani system should evolve from:

    Simple Expense Tracker

to:

    Expense Management
          +
    Accounting
          +
    GST
          +
    TDS
          +
    Fixed Assets
          +
    Financial Reporting
          +
    CA Data
          +
    ITR Preparation Data

The core principle is:

    ENTER DATA ONCE

and let the system use that data everywhere:

    Expense
       ↓
    Payment
       ↓
    Accounting
       ↓
    Reports
       ↓
    Tax
       ↓
    CA
       ↓
    ITR