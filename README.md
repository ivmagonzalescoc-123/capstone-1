# Azucena Dental Clinic - Web-Based Appointment System

Modern, multi-branch dental appointment management system with role-based access control and modular OOP architecture.

## ✨ Latest Updates

- **Simplified Odontogram**: Replaced 3D Three.js implementation with lightweight 2D clickable tooth grid
- **Modal Integration**: Odontogram now integrated as a modal in the consultation table
- **Consolidated Navigation**: Navbar and sidebar integrated into each role's main index.php
- **Cleaner Codebase**: Removed unused files, guides, and 3D dependencies

## 🏗️ Project Structure

```
capstone/
├── README.md                           # Project documentation
│
├── assets/
│   ├── css/                           # Centralized stylesheets
│   │   ├── admin.css                  # Admin dashboard styles
│   │   ├── doctor.css                 # Doctor portal styles
│   │   ├── secretary.css              # Secretary portal styles
│   │   ├── patient.css                # Patient portal styles
│   │   ├── sidebar.css                # Sidebar & navbar styles
│   │   └── style.css                  # Base styles
│   │
│   ├── api/                           # RESTful API endpoints
│   │   └── auth/                      # Authentication services
│   │       ├── login_api.php
│   │       ├── register_api.php
│   │       └── logout_api.php
│   │
│   ├── img/                           # Images and media
│   ├── js/                            # JavaScript modules
│   └── vendor/                        # Third-party libraries (Bootstrap, ApexCharts, etc.)
│
├── admin/                             # Admin Portal
│   ├── index.php                      # Unified layout (navbar + sidebar + content)
│   ├── modules/                       # Feature modules
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   ├── doctors.php
│   │   ├── branches.php
│   │   └── ...
│   └── ajax/                          # AJAX handlers
│
├── doctor/                            # Doctor Portal
│   ├── index.php                      # Unified layout
│   ├── modules/                       # Feature modules
│   │   ├── dashboard.php
│   │   ├── consultation.php           # Includes odontogram modal
│   │   ├── odontogram-modal.php       # 2D clickable teeth component
│   │   ├── appointments.php
│   │   └── ...
│   └── ajax/                          # AJAX handlers
│       ├── save-odontogram.php        # Save tooth records
│       └── get-odontogram.php         # Retrieve tooth records
│
├── secretary/                         # Secretary Portal
│   ├── index.php                      # Unified layout
│   ├── modules/                       # Feature modules
│   │   ├── dashboard.php
│   │   ├── appointments.php
│   │   ├── billing.php
│   │   └── ...
│   └── ajax/
│
├── patient/                           # Patient Portal
│   ├── index.php                      # Unified layout
│   └── modules/                       # Feature modules
│       ├── dashboard.php
│       ├── book-appointment.php
│       ├── my-appointments.php
│       └── ...
│
├── config/
│   └── config.php                     # Database configuration
│
├── database/
│   ├── azucena_dental_complete.sql   # Complete schema
│   └── migrations/
│
└── uploads/                           # User uploads (documents, images)
    ├── medical_documents/
    └── profile_pictures/
```

## 🚀 Quick Start

### 1. Database Setup

```bash
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: azucena_dental
3. Import: database/azucena_dental_complete.sql
```

### 2. Start the System

**Access via:**
```
http://localhost/capstone/
```

**Test Credentials:**
```
Admin:     admin@azucenadental.com / admin123
Doctor:    doctor@clinic.com / doctor123
Secretary: secretary@clinic.com / secretary123
Patient:   patient@email.com / patient123
```

## 👥 User Roles

| Role | URL | Purpose |
|------|-----|---------|
| **Admin** | `/admin/index.php` | System management, branches, users, reports |
| **Doctor** | `/doctor/index.php` | Appointments, patient consultations, odontogram |
| **Secretary** | `/secretary/index.php` | Booking, check-in, billing, follow-ups |
| **Patient** | `/patient/index.php` | Book appointments, view records |

## 🦷 Odontogram Feature

The simplified 2D odontogram replaces the previous 3D implementation:

### Features:
- **32-tooth grid** - Click any tooth to record status
- **Color-coded statuses**:
  - 🟢 Green: Healthy
  - 🟠 Orange: Caries
  - 🔵 Cyan: Filled
  - 💎 Light Cyan: Crown
  - 🔴 Red: Missing
- **Modal integration** - Accessible from consultation table
- **Save/Load** - Persists to database automatically

### Usage:
1. Doctor opens consultation queue
2. Click "Odontogram" button next to patient
3. Modal opens with 32-tooth grid
4. Click tooth to select, then click status button
5. "Save Odontogram" persists to database

### Files:
- `doctor/modules/odontogram-modal.php` - UI component
- `doctor/ajax/save-odontogram.php` - Save endpoint
- `doctor/ajax/get-odontogram.php` - Retrieve endpoint

## 🏗️ Architecture

### Unified Layout Pattern

Each role portal (`admin/`, `doctor/`, `secretary/`, `patient/`) follows this structure:

```php
// index.php - Unified container
<?php session_start(); require_auth(); ?>
<html>
    <!-- NAVBAR (consolidated header) -->
    <nav class="navbar">...</nav>
    
    <!-- MAIN LAYOUT -->
    <div class="sidebar-wrapper">
        <!-- SIDEBAR (navigation menu) -->
        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li><a href="?module=dashboard">Dashboard</a></li>
                <li><a href="?module=consultation">Consultation</a></li>
                ...
            </ul>
        </aside>
        
        <!-- MAIN CONTENT (loads modules via GET) -->
        <main class="main-content">
            <?php include "modules/$_GET['module'].php"; ?>
        </main>
    </div>
</html>
```

**Module Access:**
```
/doctor/index.php                      # Dashboard (default)
/doctor/index.php?module=consultation  # Consultation module
/doctor/index.php?module=appointments  # Appointments module
```

### Database Schema (23 Tables)

```
Users:           User_Account, User_Access_Modules, User_Logs
Patients:        Patients, Medical_History
Appointments:    Appointments, Schedule, Check_In_Out
Treatments:      Treatments, Selected_Treatments, Prescriptions
Dental:          Tooth_Records (tooth status & odontogram data)
Services:        Services, Selected_Services
Billing:         Billing, Payment
System:          Status, Role, Specialization, Branch, Audit_Trails
```

## 🎨 UI Features

### Navbar
- Role-based branding (Admin/Doctor/Secretary/Patient)
- User info display
- Quick logout button

### Sidebar
- Icon-based navigation
- Active page highlighting
- Mobile-responsive toggle

### Modals
- Bootstrap 5 modals
- Odontogram for tooth records
- Responsive design

## 🔐 Security

- ✅ Prepared statements (SQL injection prevention)
- ✅ Session-based authentication  
- ✅ Role-based access control (RBAC)
- ✅ Input sanitization
- ✅ Logout session destruction

## 📊 Recent Changes

### Deleted Files (Cleanup)
- `odontogram.html` - 3D Three.js implementation
- `assets/js/odontogram.js` - 3D script
- `doctor/modules/odontogram.php` - Old module
- `forms/` - Empty folder
- All documentation files (`ODONTOGRAM_*.md`, `SETUP_GUIDE.md`, etc.)
- `index.html` - Landing page redirects to role portals
- `TEST_CREDENTIALS.php` - Use database credentials instead

### New Components
- `doctor/modules/odontogram-modal.php` - 2D modal component
- `doctor/ajax/save-odontogram.php` - Save endpoint
- `doctor/ajax/get-odontogram.php` - Retrieve endpoint

### Module Updates
- Removed `content-header` divs from modules (navbar handles titles)
- Streamlined consultation module with odontogram integration

## 🔄 Module Development

### Adding a New Module

1. **Create module file:**
   ```php
   // doctor/modules/new-feature.php
   <h3>New Feature Title</h3>
   <!-- Feature content -->
   ```

2. **Add sidebar link:**
   ```php
   // In index.php sidebar nav
   <li class="nav-item">
       <a class="nav-link" href="?module=new-feature">
           <i class="bi bi-icon"></i>New Feature
       </a>
   </li>
   ```

3. **Access:**
   ```
   /doctor/index.php?module=new-feature
   ```

### Creating an API Endpoint

```php
// doctor/ajax/new-feature-api.php
<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/config.php';

// Your logic here

echo json_encode(['success' => true, 'data' => $data]);
?>
```

**Call from JavaScript:**
```javascript
const response = await fetch('doctor/ajax/new-feature-api.php?id=123');
const data = await response.json();
```

## ✅ Testing

- [ ] All role logins work
- [ ] Navbar and sidebar render correctly
- [ ] Modules load via GET parameter
- [ ] Odontogram modal opens from consultation table
- [ ] Can save/retrieve tooth records
- [ ] CSS applied for all roles
- [ ] Responsive on mobile

## 📞 Support

For issues or questions:
1. Check database schema: `database/azucena_dental_complete.sql`
2. Review role-specific index files for layout patterns
3. Check module examples for implementation reference

---
**Version**: 2.0  
**Last Updated**: January 18, 2026  
**Technology**: PHP 8.x, MySQL 8.x, Bootstrap 5, JavaScript ES6  
**Status**: Production Ready ✅
