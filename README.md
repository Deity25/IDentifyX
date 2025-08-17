![Uploading Screenshot 2025-08-16 at 8.29.58 PM.png…]()

Just An Project For Hands On Skills
# IDentifyX - Smart Employee ID Card System

![ID Card Example]**( a screenshot ID card design here)***
<img width="401" height="255" alt="Screenshot 2025-08-16 at 8 04 15 PM" src="https://github.com/user-attachments/assets/bdb69774-ed16-461a-84ba-85cbb464207c" />

A complete solution for generating professional employee ID cards with QR verification, contact management, and social media integration.
Drive Link For Working Lookup ------- <<<<<<< https://drive.google.com/drive/folders/1cDUaVLaUbFpIt7NraWjN2QTkGwG5-wk5?usp=share_link >>>>>
## ✨ Features

- 🖼️ **Dynamic ID Card Generation** with employee photos<img width="1458" height="827" alt="Screenshot 2025-08-16 at 8 29 43 PM" src="https://github.com/user-attachments/assets/48025128-cbe9-4ece-bb44-9b86e2900d65" />

- 📲 **QR Code Integration** for quick verification
- 📱 **Contact & Social Media Links** (Phone, Instagram)
- 💾 **Database Backend** for employee records
- 🖨️ **Print & Download** functionality
- 🎨 **Modern UI** with Microsoft-inspired design

## 🛠️ Technologies Used

- PHP 8.0+
- MySQL
- HTML5/CSS3
- JavaScript (QRCode.js, html2canvas)
- Font Awesome Icons
- Bootstrap 5

- ## 🛠️ Installation
# IDentifyX (INSTALL MAMP AND START IT) 
### 1. Database Setup
```sql
CREATE DATABASE employee_attendance_system;
USE employee_attendance_system;

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  designation VARCHAR(100) NOT NULL,
  contact VARCHAR(20) NOT NULL,
  instagram VARCHAR(50),
  photo_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

## 🚀 Installation

1. Clone the repo:
   ```bash
   git clone https://github.com/yourusername/IDentifyX.git
