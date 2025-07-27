# Default User Accounts

This document contains the default user accounts created for the application.

## Administrator Accounts

### 🔑 **Main Administrator** (Recommended for production)
- **Email:** `admin@example.com`
- **Username:** `admin`
- **Password:** `123456`
- **Role:** Administrator
- **Status:** Active
- **Permissions:** ALL (36 permissions) ✅

### 🧪 **Test Administrator** (For development/testing)
- **Email:** `test@example.com`
- **Password:** `password`
- **Role:** Administrator
- **Status:** Active
- **Permissions:** ALL (36 permissions) ✅

## Staff Accounts (For Task Assignment Testing)

### 👤 **Staff Member 1**
- **Email:** `staff1@example.com`
- **Username:** `staff1`
- **Password:** `password123`
- **Role:** Staff
- **Task Limits:** 2-5 tasks per day
- **Available Days:** Monday-Friday
- **Status:** Active
- **Permissions:** Limited (5 permissions) - View tasks, customers, update task status

### 👤 **Staff Member 2**
- **Email:** `staff2@example.com`
- **Username:** `staff2`
- **Password:** `password123`
- **Role:** Staff
- **Task Limits:** 1-4 tasks per day
- **Available Days:** Monday-Saturday
- **Status:** Active
- **Permissions:** Limited (5 permissions) - View tasks, customers, update task status

## Social Media Manager

### 📱 **Social Media Manager**
- **Email:** `social@example.com`
- **Username:** `social_manager`
- **Password:** `password123`
- **Role:** Social Media Manager
- **Status:** Active
- **Permissions:** Moderate (9 permissions) - Create/update tasks, view customers, task types
- **Special Features:** Can use automatic task assignment

## Other Test Accounts

### 🏭 **Test Supplier**
- **Email:** `supplier@example.com`
- **Password:** `password123`
- **Role:** Supplier
- **Status:** Active
- **Permissions:** Basic (2 permissions) - View tasks only

### ✂️ **Test Tailor**
- **Email:** `tailor@example.com`
- **Password:** `password123`
- **Role:** Tailor
- **Status:** Active
- **Permissions:** Basic (2 permissions) - View tasks only

---

## 🚀 Quick Start

1. **Login as Administrator:**
   - Go to `/login`
   - Use `admin@example.com` / `123456`
   - Full system access

2. **Test Task Assignment:**
   - Login as Social Media Manager (`social@example.com` / `password123`)
   - Create new task without selecting "Assigned To"
   - System will automatically assign to available staff

3. **Test Staff Workload:**
   - Login as Staff Member (`staff1@example.com` / `password123`)
   - View assigned tasks and workload

---

## 🔐 Security Note

**⚠️ Important:** Change the default Administrator password (`123456`) in production environment for security.

---

## 🔐 **Permission System**

The system uses role-based permissions with the following structure:

### **Administrator Role (36 permissions)**
- **Full System Access:** All CRUD operations on all resources
- **User Management:** Create, update, delete users and assign roles
- **Task Management:** Complete control over tasks, assignments, and types
- **Customer Management:** Full customer data management
- **Role Management:** Create and modify roles and permissions

### **Social Media Manager Role (9 permissions)**
- **Task Operations:** Create, view, update tasks and task status
- **Customer Access:** View customer information
- **Task Types:** View available task types
- **Special Feature:** Automatic task assignment to staff

### **Staff Role (5 permissions)**
- **Task View/Update:** View assigned tasks and update status
- **Customer View:** Access customer information for assigned tasks
- **Limited Access:** Cannot create or delete tasks

### **Supplier/Tailor Roles (2 permissions each)**
- **Basic View:** Can only view task lists and individual tasks
- **No Modifications:** Cannot create, update, or delete anything

### **Permission Categories:**
- **Customer:** View, Create, Update, Delete, Restore, Force Delete (7 permissions)
- **Tasks:** View, Create, Update, Update Status, Delete, Restore, Force Delete (8 permissions)
- **Task Types:** View, Create, Update, Delete, Restore, Force Delete (7 permissions)
- **Users:** View, Create, Update, Delete, Restore, Force Delete (7 permissions)
- **Roles:** View, Create, Update, Delete, Restore, Force Delete (7 permissions)

---

## 📝 Notes

- All users have `active` status
- Staff members have predefined task limits for automatic assignment testing
- Social Media Manager can create tasks with automatic assignment to staff
- Users can be managed through the admin panel after logging in as Administrator
- **All permissions are automatically granted to Administrator role through seeders**
- Permission verification can be run using: `php artisan db:seed --class=VerifyPermissionsSeeder`
- Grant all permissions manually: `php artisan permissions:grant-all`
- Grant all permissions to specific role: `php artisan permissions:grant-all "Role Name"` 